<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class IsolateAdminSession
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->is('admin', 'admin/*');
        $original = config('session');
        $guard = config('auth.defaults.guard');
        config([
            'session.cookie' => $original['cookie'] . ($admin ? '_admin' : ''),
            'session.path' => $admin ? '/admin' : $original['path'],
        ]);
        // Also select a distinct remember-me cookie namespace via the guard name.
        Auth::shouldUse($admin ? 'admin' : 'web');
        app('session')->driver()->setName(config('session.cookie'));
        URL::formatPathUsing(function ($path, $route) use ($admin) {
            return $admin && !str_starts_with($path, '/admin/') && $path !== '/admin'
                ? '/admin' . $path : $path;
        });
        try {
            return $next($request);
        } finally {
            config(['session' => $original]);
            Auth::shouldUse($guard);
            URL::formatPathUsing(fn ($path) => $path);
        }
    }
}
