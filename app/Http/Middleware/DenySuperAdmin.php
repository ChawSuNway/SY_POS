<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Super Admin ကို ဤ action မှ တားမြစ်သည်။
 * Super Admin သည် ဆိုင်များကို စီမံနိုင်သော်လည်း ကိုယ်တိုင် ရောင်းချခြင်း (POS) မပြုနိုင်။
 */
class DenySuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            abort(403, 'Super Admin သည် ရောင်းချ၍ မရပါ / Super Admin cannot make sales.');
        }

        return $next($request);
    }
}
