<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;

class EnsureUserIsManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $level = $user->accountType;
        $isTemporaryManager = $user->isTemporaryManager ? true : false;
        if ($level != "sysadmin" && $level != "lmanager" && !$isTemporaryManager) {
            abort(403, 'You are not authorised to access this.');
        }

        return $next($request);
    }
}
