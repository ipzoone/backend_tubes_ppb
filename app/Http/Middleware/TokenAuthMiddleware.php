<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            // Check in query parameter as a fallback
            $token = $request->query('api_token');
        }

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized: Token tidak ditemukan.'
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized: Token tidak valid atau kedaluwarsa.'
            ], 401);
        }

        // Set the authenticated user for the current request cycle
        Auth::setUser($user);

        return $next($request);
    }
}
