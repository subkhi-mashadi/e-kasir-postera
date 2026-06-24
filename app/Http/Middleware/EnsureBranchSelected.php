<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Owner/manager without a fixed branch must pick one per session
        if (! session('branch_id') && ! $user->branch_id) {
            return redirect()->route('branch.select');
        }

        return $next($request);
    }
}
