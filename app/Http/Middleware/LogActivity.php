<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * User လုပ်ဆောင်ချက်များ (create/update/delete) ကို မှတ်တမ်းတင်သည်။
 *
 * Performance — terminate() ဖြင့် response ကို client သို့ ပို့ပြီးမှ run သောကြောင့်
 * user ဘက်တွင် နှေးမသွား။ write request (POST/PUT/PATCH/DELETE) များကိုသာ၊
 * DB::table insert တစ်ခုတည်းဖြင့် (Eloquent event overhead မရှိ) မှတ်တမ်းတင်သည်။
 */
class LogActivity
{
    /** GET/read များ မမှတ် — noise + performance */
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $user = $request->user();
        if (! $user) {
            return;   // guest — login မဝင်ရသေး
        }

        if (! in_array($request->method(), self::WRITE_METHODS, true)) {
            return;   // read request — မမှတ်
        }

        // မအောင်မြင်သော request (validation/permission fail) မမှတ်
        if ($response->getStatusCode() >= 400) {
            return;
        }
        if ($request->hasSession() && $request->session()->get('errors')) {
            return;   // validation redirect-back
        }

        $route  = $request->route();
        $action = $route?->getName() ?? strtolower($request->method()).'.'.$request->path();

        // route parameter မှ subject (model) id ရယူ
        $subjectType = null;
        $subjectId   = null;
        if ($route) {
            foreach ($route->parameters() as $val) {
                if (is_object($val) && method_exists($val, 'getKey')) {
                    $subjectType = class_basename($val);
                    $subjectId   = $val->getKey();
                    break;
                }
                if (is_scalar($val)) {
                    $subjectId = is_numeric($val) ? (int) $val : null;
                    break;
                }
            }
        }

        try {
            DB::table('activity_logs')->insert([
                'user_id'      => $user->id,
                'shop_id'      => current_shop_id(),
                'action'       => Str::limit($action, 78, ''),
                'method'       => $request->method(),
                'path'         => Str::limit($request->path(), 250, ''),
                'subject_type' => $subjectType,
                'subject_id'   => is_numeric($subjectId) ? (int) $subjectId : null,
                'ip'           => $request->ip(),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // logging က app ကို ဘယ်တော့မှ မကျစေရ
            report($e);
        }
    }
}
