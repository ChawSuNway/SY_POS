<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Super Admin — ဆိုင်အားလုံး ခြုံငုံ dashboard
        if (auth()->user()->isSuperAdmin()) {
            return $this->superDashboard();
        }

        $today = Carbon::today();

        $todaySales = Sale::whereDate('sold_at', $today)->sum('total');
        $todayProfit = Sale::whereDate('sold_at', $today)->sum('profit');
        $todayCount = Sale::whereDate('sold_at', $today)->count();

        $productCount = Product::where('is_active', true)->count();

        $pendingOrders = Order::where('status', 'pending')->count();

        $lowStock = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->get();

        // last 7 days revenue for a mini chart
        $trend = Sale::selectRaw('DATE(sold_at) as d, SUM(total) as revenue, SUM(profit) as profit')
            ->where('sold_at', '>=', $today->copy()->subDays(6))
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $days = collect(range(6, 0))->map(function ($i) use ($today, $trend) {
            $date = $today->copy()->subDays($i)->toDateString();
            return [
                'date'    => $date,
                'revenue' => (float) ($trend[$date]->revenue ?? 0),
                'profit'  => (float) ($trend[$date]->profit ?? 0),
            ];
        });

        $recentSales = Sale::with('user')->latest('sold_at')->limit(8)->get();

        return view('dashboard', compact(
            'todaySales', 'todayProfit', 'todayCount',
            'productCount', 'pendingOrders', 'lowStock', 'days', 'recentSales'
        ));
    }

    /** Super Admin — ဆိုင်တိုင်း၏ ယနေ့ရောင်းအား + အနှစ်ချုပ် */
    private function superDashboard()
    {
        $today = Carbon::today();

        $shops = Shop::withCount(['users', 'products'])->orderBy('id')->get();

        // ဆိုင်အလိုက် ယနေ့ ရောင်းအား (scope ကို ကျော်၍ အားလုံး)
        $salesToday = Sale::withoutGlobalScope('shop')
            ->whereDate('sold_at', $today)
            ->selectRaw('shop_id, SUM(total) as revenue, SUM(profit) as profit, COUNT(*) as cnt')
            ->groupBy('shop_id')->get()->keyBy('shop_id');

        $totals = [
            'shops'    => $shops->count(),
            'active'   => $shops->where('is_active', true)->count(),
            'revenue'  => $salesToday->sum('revenue'),
            'sales'    => $salesToday->sum('cnt'),
        ];

        return view('dashboard_super', compact('shops', 'salesToday', 'totals', 'today'));
    }
}
