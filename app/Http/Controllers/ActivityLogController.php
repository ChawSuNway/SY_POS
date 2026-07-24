<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * လုပ်ဆောင်ချက် မှတ်တမ်း — Super Admin ကသာ ကြည့်နိုင် (route middleware role:super_admin)။
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user:id,name,role', 'shop:id,name,name_en'])
            ->latest('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->integer('shop_id'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', $request->string('action').'%');
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $shops = Shop::orderBy('name')->get(['id', 'name', 'name_en']);

        return view('activity_logs.index', compact('logs', 'users', 'shops'));
    }
}
