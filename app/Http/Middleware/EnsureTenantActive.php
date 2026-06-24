<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $company = $user->company;

        if (! $company || ! $company->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun usaha tidak aktif.']);
        }

        $subscription = $company->subscription;

        if (! $subscription || ! $subscription->isActive()) {
            // Allow access to billing/subscription pages
            if ($request->routeIs('subscription.*')) {
                return $next($request);
            }
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
