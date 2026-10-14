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
        $context = (string) $request->route('context', '');
        $prefix = $context !== '' ? '/session/' . $context : ($admin ? '/admin' : '');
        $original = config('session');
        $guard = config('auth.defaults.guard');
        $guards = config('auth.guards');
        $guardName = $context !== '' ? 'isolated_' . str_replace('-', '', $context) : ($admin ? 'admin' : 'web');
        if ($context !== '') {
            // The UUID is a public routing context, never an authentication token.
            config(['auth.guards.' . $guardName => ['driver' => 'session', 'provider' => 'users']]);
        }
        config([
            'session.cookie' => $original['cookie'] . ($context !== '' ? '_tab_' . str_replace('-', '', $context) : ($admin ? '_admin' : '')),
            'session.path' => $prefix !== '' ? $prefix : $original['path'],
        ]);
        $request->attributes->set('session_prefix', $prefix);
        if ($context !== '') {
            // Context selects the session, not a controller business argument.
            $request->route()->forgetParameter('context');
        }
        app('cookie')->setDefaultPathAndDomain(config('session.path'), $original['domain'], $original['secure'], $original['same_site']);
        // Also select a distinct remember-me cookie namespace via the guard name.
        Auth::shouldUse($guardName);
        app('session')->driver()->setName(config('session.cookie'));
        URL::formatPathUsing(function ($path, $route) use ($prefix) {
            return $prefix !== '' && !str_starts_with($path, '/admin/') && !str_starts_with($path, '/session/') && $path !== '/admin'
                ? $prefix . $path : $path;
        });
        try {
            return $next($request);
        } finally {
            config(['session' => $original]);
            config(['auth.guards' => $guards]);
            app('cookie')->setDefaultPathAndDomain($original['path'], $original['domain'], $original['secure'], $original['same_site']);
            Auth::shouldUse($guard);
            URL::formatPathUsing(fn ($path) => $path);
        }
    }
}
