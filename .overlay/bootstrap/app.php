<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy in front of this container (Caddy) so
        // Laravel knows the original request was HTTPS, the real client IP,
        // and the real host header. Without this, url()/asset() generate
        // http:// links even though the site is served over https://,
        // causing the browser to block them as mixed content — this is
        // exactly what broke Filament's admin CSS/JS.
        // Safe to trust "*" here because the app container is never reached
        // directly from the internet — only other containers on the same
        // private Docker network (Caddy included) can reach it at all.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
