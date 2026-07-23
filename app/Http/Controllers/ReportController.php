<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    /** [$from, $to] Carbon range — default: ယခုလ */
    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with('user')
            ->whereBetween('sold_at', [$from, $to])
            ->orderBy('sold_at')
            ->get();

        $totals = [
            'count'    => $sales->count(),
            'subtotal' => $sales->sum('subtotal'),
            'discount' => $sales->sum('discount'),
            'total'    => $sales->sum('total'),
            'cost'     => $sales->sum('total_cost'),
            'profit'   => $sales->sum('profit'),
        ];

        return view('reports.sales', compact('sales', 'totals', 'from', 'to'));
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->range($request);

        $purchases = Purchase::with('user')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('purchase_date')
            ->get();

        $totals = [
            'count' => $purchases->count(),
            'total' => $purchases->sum('total_cost'),
        ];

        return view('reports.purchases', compact('purchases', 'totals', 'from', 'to'));
    }

    /** အရှုံးအမြတ် — product / brand / category အလိုက် ခွဲခြမ်း */
    public function profit(Request $request)
    {
        [$from, $to] = $this->range($request);
        $groupBy = $request->input('group', 'product'); // product | brand | category | type

        $items = SaleItem::with(['product.category', 'product.brand'])
            ->whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to]))
            ->get();

        $grouped = $items->groupBy(function ($item) use ($groupBy) {
            $p = $item->product;
            return match ($groupBy) {
                'brand'    => optional($p->brand)->name ?? '-',
                'category' => optional($p->category)->name ?? '-',
                'type'     => $p->type === 'rice' ? 'ဆန် (Rice)' : 'ဆီ (Oil)',
                default    => $p->displayName(),
            };
        })->map(function ($rows, $label) {
            $revenue = $rows->sum('line_total');
            $cost    = $rows->sum('line_cost');
            return [
                'label'    => $label,
                'qty_base' => $rows->sum('qty_base'),
                'revenue'  => $revenue,
                'cost'     => $cost,
                'profit'   => $revenue - $cost,
            ];
        })->sortByDesc('profit')->values();

        $totals = [
            'revenue' => $grouped->sum('revenue'),
            'cost'    => $grouped->sum('cost'),
            'profit'  => $grouped->sum('profit'),
        ];

        return view('reports.profit', compact('grouped', 'totals', 'from', 'to', 'groupBy'));
    }

    /** လက်ကျန်စာရင်း — လက်ရှိ stock နှင့် တန်ဖိုး */
    public function inventory(Request $request)
    {
        $query = Product::with(['category', 'brand', 'units']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $products = $query->orderBy('type')->orderBy('category_id')->get();

        $totalValue = $products->sum(fn ($p) => $p->stockValue());

        return view('reports.inventory', compact('products', 'totalValue'));
    }

    public function lowStock()
    {
        $products = Product::with(['category', 'brand', 'units'])
            ->where('is_active', true)
            ->where('low_stock_threshold', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('type')
            ->get();

        return view('reports.low_stock', compact('products'));
    }
}
