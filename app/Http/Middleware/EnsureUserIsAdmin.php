<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
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
        if ($level != "sysadmin") {
            //abort(403, 'You are not authorised to access this.');

            return redirect("/home")->with([
                'customError' => 'You are not authorised to access this page.'
            ]);
        }
        return $next($request);
    }
}
