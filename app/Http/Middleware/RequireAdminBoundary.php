<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAdminBoundary
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*') && $request->user()) {
            abort_unless((int) ($request->user()->is_active ?? 1) === 1 && $request->user()->hasAnyRole(['admin', 'osa']), 403);
        }
        return $next($request);
    }
}
