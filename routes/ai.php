<?php

use App\Http\Middleware\AuthenticateAdminMcpToken;
use App\Mcp\Servers\BagistoAdminServer;
use Laravel\Mcp\Facades\Mcp;

// Web / HTTP Transport for remote AI clients (Claude Desktop, Cursor, Custom Agents)
Mcp::web('/mcp/admin', BagistoAdminServer::class)->middleware([
    AuthenticateAdminMcpToken::class,
]);

// Local transport for CLI stdio execution (php artisan mcp:start admin)
Mcp::local('admin', BagistoAdminServer::class);
