<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Accepts multiple roles as separate arguments OR comma-separated string.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Flatten koma-separated roles menjadi array
        $flatRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $flatRoles[] = trim($r);
            }
        }

        foreach ($flatRoles as $role) {
            if (auth()->user()->hasRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. You do not have the required role.'], 403);
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
    }
}
