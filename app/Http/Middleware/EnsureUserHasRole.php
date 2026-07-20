<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage: ->middleware('role:manager')  (manager နှင့် အထက် — admin ပါ ခွင့်ပြု)
     *        ->middleware('role:admin')
     *        ->middleware('role:cashier')  (login ဝင်ထားသူ အားလုံး)
     */
    public function handle(Request $request, Closure $next, string $minRole): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'အကောင့် ပိတ်ထားသည် / Account inactive.');
        }

        if (! $user->hasAtLeast($minRole)) {
            abort(403, 'ဤစာမျက်နှာသို့ ဝင်ရောက်ခွင့် မရှိပါ / You do not have permission.');
        }

        return $next($request);
    }
}
