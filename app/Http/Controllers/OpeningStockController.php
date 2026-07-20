<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /** ဖွင့်လှစ်လက်ကျန် တင်သည့် screen */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'units'])->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$term}%"));
            });
        }

        $products = $query->orderBy('type')->orderBy('category_id')->get();

        return view('opening_stock.index', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rows'                => ['required', 'array'],
            'rows.*.qty'          => ['nullable', 'integer', 'min:0'],
            'rows.*.unit_cost'    => ['nullable', 'numeric', 'min:0'],
            'rows.*.unit_id'      => ['nullable', 'integer'],
        ]);

        $userId = auth()->id();
        $count = 0;

        foreach ($data['rows'] as $productId => $row) {
            $qty = (float) ($row['qty'] ?? 0);
            if ($qty <= 0) {
                continue;   // ဖြည့်မထားသော row များ ကျော်
            }

            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            // ရွေးထားသော unit ၏ factor ဖြင့် base unit သို့ ပြောင်း
            $factor = 1.0;
            if (! empty($row['unit_id'])) {
                $unit = ProductUnit::where('product_id', $product->id)->find($row['unit_id']);
                if ($unit) {
                    $factor = (float) $unit->factor;
                }
            }

            $qtyBase      = $qty * $factor;
            $unitCost     = (float) ($row['unit_cost'] ?? 0);
            $unitCostBase = $factor > 0 ? $unitCost / $factor : 0;

            $this->inventory->setOpeningStock($product, $qtyBase, $unitCostBase, $userId);
            $count++;
        }

        if ($count === 0) {
            return back()->with('error', __('app.opening_none'));
        }

        return redirect()->route('opening-stock.index')
            ->with('success', __('app.opening_saved', ['count' => $count]));
    }
}
