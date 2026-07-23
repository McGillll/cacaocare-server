<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedActionException;
use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || $request->user()->role !== $role) {
            throw new UnauthorizedActionException("This action requires the {$role} role.");
        }

        return $next($request);
    }
}
