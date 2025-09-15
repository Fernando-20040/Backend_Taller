<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->rol !== 'admin') {
            return response()->json(['message' => 'No autorizado: se requiere rol admin.'], 403);
        }
        return $next($request);
    }
}
