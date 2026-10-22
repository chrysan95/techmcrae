<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->role !== $role) {
            Log::warning('Access denied', [
                'employee_id' => Auth::user()->employee_id,
                'required'    => $role,
                'path'        => $request->path(),
            ]);
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
