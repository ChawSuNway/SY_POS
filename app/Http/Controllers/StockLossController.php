<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLoss;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** ပျက်စီး/ဆုံးရှုံး စာရင်း — manager နှင့် အထက် */
class StockLossController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $query = StockLoss::with(['product.category.parent', 'product.brand', 'user'])
            ->latest('lost_at')->latest('id');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('reason', 'like', "%{$term}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('product.category', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('product.brand', fn ($b) => $b->where('name', 'like', "%{$term}%"));
            });
        }

        $losses = $query->paginate(20)->withQueryString();

        $totalValue = StockLoss::sum('loss_value');
        $monthValue = StockLoss::whereBetween('lost_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('loss_value');

        return view('losses.index', compact('losses', 'totalValue', 'monthValue'));
    }

    public function create()
    {
        $products = Product::with(['units' => fn ($q) => $q->where('is_active', true), 'category.parent', 'brand'])
            ->where('is_active', true)
            ->orderBy('type')->orderBy('category_id')->get();

        return view('losses.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lost_at'    => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'unit_id'    => ['nullable', 'integer'],
            'qty'        => ['required', 'integer', 'min:1'],
            'reason'     => ['required', 'string', 'max:200'],
        ]);

        $product = Product::with('units')->findOrFail($data['product_id']);

        // ရွေးထားသော unit (မရွေးလျှင် base unit)
        $factor = 1.0;
        $unitLabel = $product->base_unit;
        if (! empty($data['unit_id'])) {
            $unit = $product->units->firstWhere('id', (int) $data['unit_id']);
            if ($unit) {
                $factor = (float) $unit->factor;
                $unitLabel = $unit->label;
            }
        }

        $qty = (float) $data['qty'];
        $qtyBase = $qty * $factor;

        // လက်ကျန် လုံလောက်မှု စစ်
        if ($qtyBase > (float) $product->stock + 0.0001) {
            return back()->withInput()->with('error',
                __('app.loss_insufficient', ['stock' => qty_fmt($product->stock).' '.$product->base_unit]));
        }

        DB::transaction(function () use ($data, $product, $qty, $qtyBase, $factor, $unitLabel, $request) {
            $loss = new StockLoss([
                'lost_at'    => $data['lost_at'],
                'product_id' => $product->id,
                'unit_label' => $unitLabel,
                'factor'     => $factor,
                'qty'        => $qty,
                'qty_base'   => $qtyBase,
                'reason'     => $data['reason'],
                'user_id'    => $request->user()->id,
                'unit_cost_base' => 0,
                'loss_value' => 0,
            ]);
            $loss->save();

            $costBase = $this->inventory->recordLoss(
                $product, $qtyBase, $request->user()->id, $loss,
                'ပျက်စီး/ဆုံးရှုံး — '.$data['reason']
            );

            $loss->update([
                'unit_cost_base' => $costBase,
                'loss_value'     => $qtyBase * $costBase,
            ]);
        });

        return redirect()->route('losses.index')->with('success', __('app.saved'));
    }

    public function destroy(Request $request, StockLoss $loss)
    {
        DB::transaction(function () use ($loss, $request) {
            // လက်ကျန် ပြန်ထည့် (မှတ်တမ်းတင်ချိန် cost ဖြင့် weighted-avg ပြန်ပေါင်း)
            $this->inventory->restoreStock(
                $loss->product,
                (float) $loss->qty_base,
                (float) $loss->unit_cost_base,
                $request->user()->id,
                'ပျက်စီးစာရင်း ပယ်ဖျက် — '.$loss->reason
            );
            $loss->delete();
        });

        return back()->with('success', __('app.deleted'));
    }
}
