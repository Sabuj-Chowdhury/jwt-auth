<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth('api')->user();

        if (!$user || !$user->role_id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $hasPermission = $user->role
            ->permissions()
            ->where('slug', $permission)
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
