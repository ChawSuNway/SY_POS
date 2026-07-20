<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
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
}
