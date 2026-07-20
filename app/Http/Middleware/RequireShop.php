<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ဆိုင်နှင့် သက်ဆိုင်သော screen များ — shop context (shop_id) မရှိသူ (super_admin) ကို
 * ဆိုင်စီမံ စာမျက်နှာသို့ ပြန်လွှဲသည်။ Super Admin = manage-only။
 */
class RequireShop
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->shop_id) {
            return redirect()->route('shops.index');
        }

        return $next($request);
    }
}
