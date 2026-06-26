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
        // Restrict to Cloudflare + local proxies only — prevents X-Forwarded-For spoofing
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            // Cloudflare IPv4 ranges
            '103.21.244.0/22','103.22.200.0/22','103.31.4.0/22',
            '104.16.0.0/13','104.24.0.0/14','108.162.192.0/18',
            '131.0.72.0/22','141.101.64.0/18','162.158.0.0/15',
            '172.64.0.0/13','173.245.48.0/20','188.114.96.0/20',
            '190.93.240.0/20','197.234.240.0/22','198.41.128.0/17',
        ]);
        $middleware->alias([
            'tenant.active'    => \App\Http\Middleware\EnsureTenantActive::class,
            'branch.selected'  => \App\Http\Middleware\EnsureBranchSelected::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/midtrans',
            'api/sync/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
