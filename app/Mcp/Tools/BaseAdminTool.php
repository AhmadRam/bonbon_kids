<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Webkul\User\Models\Admin;

abstract class BaseAdminTool extends Tool
{
    /**
     * Get the authenticated admin user.
     */
    protected function admin(): ?Admin
    {
        $user = auth()->guard('admin')->user();

        if ($user instanceof Admin) {
            return $user;
        }

        return null;
    }

    /**
     * Check if the authenticated admin has the specified permission.
     * Returns an error Response if unauthorized, or null if authorized.
     */
    protected function checkPermission(string $permission): ?Response
    {
        $admin = $this->admin();

        if (! $admin) {
            return Response::error('Unauthenticated: No active admin session found for this tool request.');
        }

        if (function_exists('bouncer') && ! bouncer()->hasPermission($permission)) {
            return Response::error("Unauthorized: Admin [{$admin->email}] lacks the [{$permission}] permission.");
        }

        return null;
    }

    /**
     * Return a standardized successful JSON response.
     */
    protected function successResponse(string $message, array $data = []): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return a standardized error response.
     */
    protected function errorResponse(string $message, array $errors = []): Response
    {
        return Response::error(json_encode([
            'success' => false,
            'error' => $message,
            'details' => $errors,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
