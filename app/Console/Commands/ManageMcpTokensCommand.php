<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\User\Models\Admin;

class ManageMcpTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:token
                            {action=create : Action to perform: create, list, or revoke}
                            {email? : Admin user email}
                            {--name=MCP-Assistant : Name or description for the token}
                            {--token-id= : Specific token ID to revoke}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage API tokens for Model Context Protocol (MCP) AI clients';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = strtolower($this->argument('action'));
        $email = $this->argument('email');

        return match ($action) {
            'create' => $this->handleCreate($email),
            'list' => $this->handleList($email),
            'revoke' => $this->handleRevoke($email),
            default => $this->handleInvalidAction($action),
        };
    }

    protected function handleCreate(?string $email): int
    {
        if (! $email) {
            $email = $this->ask('Enter the Admin email address:');
        }

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("Admin with email [{$email}] not found.");

            return self::FAILURE;
        }

        if (! $admin->status) {
            $this->error("Admin [{$email}] is deactivated. Activate the account in the Admin Panel first.");

            return self::FAILURE;
        }

        $tokenName = $this->option('name') ?: 'MCP-Assistant';
        $token = $admin->createToken($tokenName);

        $this->info('================================================================');
        $this->info('  MCP Access Token Generated Successfully');
        $this->info('================================================================');
        $this->line("Admin: {$admin->name} ({$admin->email})");
        $this->line("Role:  {$admin->role?->name}");
        $this->line("Token Name: {$tokenName}");
        $this->newLine();
        $this->warn("Bearer Token (Copy this now, it won't be shown again):");
        $this->line($token->plainTextToken);
        $this->newLine();

        $appUrl = rtrim(config('app.url', 'http://localhost:8080'), '/');
        $mcpUrl = "{$appUrl}/mcp/admin";

        $this->info('--- Configuration for Claude Desktop (claude_desktop_config.json) ---');
        $claudeConfig = json_encode([
            'mcpServers' => [
                'bagisto-store' => [
                    'url' => $mcpUrl,
                    'headers' => [
                        'Authorization' => "Bearer {$token->plainTextToken}",
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->line($claudeConfig);
        $this->newLine();

        $this->info('--- Configuration for Cursor (Settings > Features > MCP) ---');
        $this->line('Type: SSE (or Streamable HTTP)');
        $this->line("URL: {$mcpUrl}");
        $this->line("Headers: Authorization: Bearer {$token->plainTextToken}");
        $this->newLine();

        return self::SUCCESS;
    }

    protected function handleList(?string $email): int
    {
        $query = Admin::query();

        if ($email) {
            $query->where('email', $email);
        }

        $admins = $query->with('tokens')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($admins as $admin) {
            foreach ($admin->tokens as $token) {
                $rows[] = [
                    'ID' => $token->id,
                    'Admin' => "{$admin->name} ({$admin->email})",
                    'Name' => $token->name,
                    'Last Used At' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
                    'Created At' => $token->created_at?->toDateTimeString(),
                ];
            }
        }

        if (empty($rows)) {
            $this->info('No MCP tokens found.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Admin', 'Token Name', 'Last Used', 'Created At'], $rows);

        return self::SUCCESS;
    }

    protected function handleRevoke(?string $email): int
    {
        $tokenId = $this->option('token-id');

        if ($tokenId) {
            PersonalAccessToken::where('id', $tokenId)->delete();
            $this->info("Token ID [{$tokenId}] revoked successfully.");

            return self::SUCCESS;
        }

        if (! $email) {
            $email = $this->ask('Enter admin email to revoke all their tokens:');
        }

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("Admin with email [{$email}] not found.");

            return self::FAILURE;
        }

        $count = $admin->tokens()->count();
        $admin->tokens()->delete();

        $this->info("Successfully revoked all [{$count}] token(s) for {$admin->email}.");

        return self::SUCCESS;
    }

    protected function handleInvalidAction(string $action): int
    {
        $this->error("Invalid action [{$action}]. Valid actions are: create, list, revoke.");

        return self::FAILURE;
    }
}
