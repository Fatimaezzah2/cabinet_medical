<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPatient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof User || ! $request->user()->isPatient()) {
            abort(403);
        }

        return $next($request);
    }
}
