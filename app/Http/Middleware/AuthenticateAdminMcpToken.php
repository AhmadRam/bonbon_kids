<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Webkul\User\Models\Admin;

class AuthenticateAdminMcpToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            // Support passing token as query param for SSE clients that cannot set custom headers
            $token = $request->query('token');
        }

        if (! $token) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Missing Bearer token in Authorization header or token query parameter.',
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired MCP access token.',
            ], 401);
        }

        $admin = $accessToken->tokenable;

        if (! ($admin instanceof Admin) || ! $admin->status) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'The token is not associated with an active admin account.',
            ], 403);
        }

        // Authenticate the admin across admin guard and request resolver
        auth()->guard('admin')->setUser($admin);
        auth()->setUser($admin);
        $request->setUserResolver(fn () => $admin);

        // Update token's last_used_at
        $accessToken->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
