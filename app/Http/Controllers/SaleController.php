<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /** POS screen */
    public function create()
    {
        $products = Product::with(['units' => fn ($q) => $q->where('is_active', true), 'category', 'brand'])
            ->where('is_active', true)
            ->orderBy('type')->orderBy('category_id')
            ->get();

        $customers = \App\Models\Customer::where('is_active', true)->orderBy('name')->get();

        return view('sales.pos', compact('products', 'customers'));
    }

    /** ajax — product ၏ active units + price */
    public function productUnits(Product $product)
    {
        return response()->json(
            $product->units()->where('is_active', true)->orderBy('sort_order')->get()
                ->map(fn ($u) => [
                    'id'            => $u->id,
                    'label'         => $u->label,
                    'factor'        => (float) $u->factor,
                    'selling_price' => (float) $u->selling_price,
                ])->values()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'            => ['nullable', 'exists:customers,id'],
            'customer_name'          => ['nullable', 'string', 'max:150'],
            'discount'               => ['nullable', 'numeric', 'min:0'],
            'paid_amount'            => ['nullable', 'numeric', 'min:0'],
            'note'                   => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.product_unit_id'=> ['required', 'exists:product_units,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
        ]);

        // ဖောက်သည် ရွေးထားလျှင် ၎င်း၏ အမည်ကို snapshot သိမ်း
        $customerName = $data['customer_name'] ?? null;
        if (! empty($data['customer_id'])) {
            $customerName = \App\Models\Customer::find($data['customer_id'])?->name ?? $customerName;
        }

        $sale = DB::transaction(function () use ($data, $request, $customerName) {
            $sale = Sale::create([
                'invoice_no'    => $this->nextNo(),
                'sold_at'       => now(),
                'user_id'       => $request->user()->id,
                'customer_id'   => $data['customer_id'] ?? null,
                'customer_name' => $customerName,
                'note'          => $data['note'] ?? null,
                'subtotal'      => 0,
                'discount'      => $data['discount'] ?? 0,
                'total'         => 0,
                'paid_amount'   => $data['paid_amount'] ?? 0,
                'change_amount' => 0,
                'total_cost'    => 0,
                'profit'        => 0,
            ]);

            $subtotal = 0;
            $totalCost = 0;

            foreach ($data['items'] as $row) {
                $product = Product::with('units')->findOrFail($row['product_id']);
                $unit = $product->units()->whereKey($row['product_unit_id'])->firstOrFail();

                $factor    = (float) $unit->factor;
                $qty       = (float) $row['qty'];
                $qtyBase   = $qty * $factor;
                $unitPrice = (float) $unit->selling_price;
                $lineTotal = $qty * $unitPrice;

                // လက်ကျန် စစ်ဆေး
                if ($qtyBase > (float) $product->stock + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "လက်ကျန် မလုံလောက်ပါ — {$product->displayName()} (လက်ကျန် {$product->stock} {$product->base_unit})။",
                    ]);
                }

                // လက်ကျန်လျှော့ + ရောင်းချချိန် avg cost ရယူ
                $costBase = $this->inventory->issueStock(
                    $product, $qtyBase,
                    $request->user()->id, $sale,
                    "Sale {$sale->invoice_no}"
                );
                $lineCost = $qtyBase * $costBase;

                $sale->items()->create([
                    'product_id'      => $product->id,
                    'product_unit_id' => $unit->id,
                    'unit_label'      => $unit->label,
                    'factor'          => $factor,
                    'qty'             => $qty,
                    'qty_base'        => $qtyBase,
                    'unit_price'      => $unitPrice,
                    'line_total'      => $lineTotal,
                    'unit_cost_base'  => $costBase,
                    'line_cost'       => $lineCost,
                ]);

                $subtotal  += $lineTotal;
                $totalCost += $lineCost;
            }

            $discount = (float) ($data['discount'] ?? 0);
            $total = max(0, $subtotal - $discount);
            $paid  = (float) ($data['paid_amount'] ?? 0);

            // ငွေမပြည့်ချေလျှင် အကြွေး — ပုံမှန်ဖောက်သည် (customer record) ရွေးထားမှသာ ခွင့်ပြု
            $creditDue = max(0, $total - $paid);
            if ($creditDue > 0 && empty($data['customer_id'])) {
                throw ValidationException::withMessages([
                    'customer_id' => __('app.credit_requires_customer'),
                ]);
            }

            $sale->update([
                'subtotal'      => $subtotal,
                'total'         => $total,
                'total_cost'    => $totalCost,
                'profit'        => $total - $totalCost,
                'paid_amount'   => $paid,
                'change_amount' => max(0, $paid - $total),
                'credit_due'    => $creditDue,
            ]);

            return $sale;
        });

        return redirect()->route('sales.receipt', $sale)->with('success', __('app.saved'));
    }

    public function index(Request $request)
    {
        $query = Sale::with('user')->latest('sold_at');

        // Cashier — မိမိရောင်းချမှုသာ ကြည့်နိုင်
        if ($request->user()->isCashier()) {
            $query->where('user_id', $request->user()->id);
        }
        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->to);
        }

        $sales = $query->paginate(20)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $this->authorizeView($sale);
        $sale->load(['items.product.category', 'items.product.brand', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $this->authorizeView($sale);
        $sale->load(['items.product.category', 'items.product.brand', 'user']);

        return view('sales.receipt', compact('sale'));
    }

    private function authorizeView(Sale $sale): void
    {
        if (auth()->user()->isCashier() && $sale->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function nextNo(): string
    {
        $prefix = 'INV-' . now()->format('ymd') . '-';
        $last = Sale::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')->value('invoice_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
