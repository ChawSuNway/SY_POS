<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $query = Purchase::with('user')->latest('purchase_date')->latest('id');

        if ($request->filled('from')) {
            $query->whereDate('purchase_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('purchase_date', '<=', $request->to);
        }

        $purchases = $query->paginate(20)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $products = Product::with('units')->where('is_active', true)
            ->orderBy('type')->get();

        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('products', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_date'          => ['required', 'date'],
            'supplier_id'            => ['nullable', 'exists:suppliers,id'],
            'supplier_name'          => ['nullable', 'string', 'max:150'],
            'note'                   => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.product_unit_id'=> ['nullable', 'exists:product_units,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'      => ['required', 'numeric', 'min:0'],
        ]);

        $supplierName = $data['supplier_name'] ?? null;
        if (! empty($data['supplier_id'])) {
            $supplierName = \App\Models\Supplier::find($data['supplier_id'])?->name ?? $supplierName;
        }

        $purchase = DB::transaction(function () use ($data, $request, $supplierName) {
            $purchase = Purchase::create([
                'purchase_no'   => $this->nextNo(),
                'purchase_date' => $data['purchase_date'],
                'user_id'       => $request->user()->id,
                'supplier_id'   => $data['supplier_id'] ?? null,
                'supplier_name' => $supplierName,
                'note'          => $data['note'] ?? null,
                'total_cost'    => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);

                // ယူနစ်၏ factor (product_unit ရွေးထားလျှင် ၎င်း၏ factor၊ မဟုတ်လျှင် base = 1)
                $unit = $product->units()->whereKey($row['product_unit_id'] ?? null)->first();
                $factor = $unit ? (float) $unit->factor : 1.0;
                $label  = $unit ? $unit->label : $product->base_unit;

                $qty      = (float) $row['qty'];
                $qtyBase  = $qty * $factor;
                $unitCost = (float) $row['unit_cost'];       // cost per purchase-unit
                $lineCost = $qty * $unitCost;
                $costBase = $factor > 0 ? $unitCost / $factor : $unitCost;  // cost per base unit

                $item = $purchase->items()->create([
                    'product_id'      => $product->id,
                    'product_unit_id' => $unit?->id,
                    'unit_label'      => $label,
                    'factor'          => $factor,
                    'qty'             => $qty,
                    'qty_base'        => $qtyBase,
                    'unit_cost'       => $unitCost,
                    'line_cost'       => $lineCost,
                ]);

                // လက်ကျန်တိုး + weighted-average ပြန်တွက်
                $this->inventory->receiveStock(
                    $product, $qtyBase, $costBase,
                    $request->user()->id, $purchase,
                    "Purchase {$purchase->purchase_no}"
                );

                $total += $lineCost;
            }

            $purchase->update(['total_cost' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', __('app.saved'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product.category', 'items.product.brand', 'user']);

        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        // Weighted-average ကို ပြန်မဖြေရှင်းနိုင်သဖြင့် — ဖျက်ခွင့် မပေးပါ (audit trail ထိန်းသိမ်း)
        return back()->with('error', 'အဝယ်မှတ်တမ်းကို ဖျက်၍မရပါ (လက်ကျန်/ကုန်ကျစရိတ် ထိန်းသိမ်းရန်)။');
    }

    private function nextNo(): string
    {
        $prefix = 'PUR-' . now()->format('ymd') . '-';
        $last = Purchase::where('purchase_no', 'like', $prefix . '%')
            ->orderByDesc('purchase_no')->value('purchase_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
