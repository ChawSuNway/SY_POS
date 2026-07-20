<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'customer'])->latest('order_date')->latest('id');

        $status = $request->input('status', 'pending');
        if (in_array($status, ['pending', 'delivered', 'cancelled'], true)) {
            $query->where('status', $status);
        }
        if ($request->filled('q')) {
            $query->where(fn ($sub) => $sub
                ->where('order_no', 'like', "%{$request->q}%")
                ->orWhere('customer_name', 'like', "%{$request->q}%"));
        }

        $orders = $query->paginate(20)->withQueryString();

        $counts = [
            'pending'   => Order::where('status', 'pending')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('orders.index', compact('orders', 'status', 'counts'));
    }

    public function create()
    {
        $products = Product::with(['units' => fn ($q) => $q->where('is_active', true), 'category', 'brand'])
            ->where('is_active', true)->orderBy('type')->orderBy('category_id')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('orders.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_date'             => ['required', 'date'],
            'customer_id'            => ['nullable', 'exists:customers,id'],
            'customer_name'          => ['nullable', 'string', 'max:150'],
            'delivery_address'       => ['nullable', 'string', 'max:500'],
            'discount'               => ['nullable', 'numeric', 'min:0'],
            'note'                   => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.product_unit_id'=> ['required', 'exists:product_units,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
        ]);

        $customerName = $data['customer_name'] ?? null;
        if (! empty($data['customer_id'])) {
            $customerName = Customer::find($data['customer_id'])?->name ?? $customerName;
        }

        $order = DB::transaction(function () use ($data, $request, $customerName) {
            $order = Order::create([
                'order_no'      => $this->nextNo(),
                'order_date'    => $data['order_date'],
                'customer_id'   => $data['customer_id'] ?? null,
                'customer_name' => $customerName,
                'delivery_address' => $data['delivery_address'] ?? null,
                'user_id'       => $request->user()->id,
                'status'        => Order::STATUS_PENDING,
                'discount'      => $data['discount'] ?? 0,
                'note'          => $data['note'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $row) {
                $product = Product::with('units')->findOrFail($row['product_id']);
                $unit = $product->units()->whereKey($row['product_unit_id'])->firstOrFail();

                $factor    = (float) $unit->factor;
                $qty       = (float) $row['qty'];
                $unitPrice = (float) $unit->selling_price;
                $lineTotal = $qty * $unitPrice;

                $order->items()->create([
                    'product_id'      => $product->id,
                    'product_unit_id' => $unit->id,
                    'unit_label'      => $unit->label,
                    'factor'          => $factor,
                    'qty'             => $qty,
                    'qty_base'        => $qty * $factor,
                    'unit_price'      => $unitPrice,
                    'line_total'      => $lineTotal,
                ]);
                $subtotal += $lineTotal;
            }

            $discount = (float) ($data['discount'] ?? 0);
            $order->update([
                'subtotal' => $subtotal,
                'total'    => max(0, $subtotal - $discount),
            ]);

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', __('app.saved'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product.category', 'items.product.brand', 'user', 'deliveredBy', 'customer', 'sale']);

        return view('orders.show', compact('order'));
    }

    /** ပေးပို့ပြီး ⇒ Sale အဖြစ် ပြောင်း (လက်ကျန်လျှော့ + အမြတ်တွက်) */
    public function deliver(Request $request, Order $order)
    {
        if (! $order->isPending()) {
            return back()->with('error', 'ဤ order ကို ပေးပို့၍/ပယ်ဖျက်၍ ပြီးသွားပါပြီ။');
        }

        DB::transaction(function () use ($order, $request) {
            $order->load('items.product');

            // လက်ကျန် အားလုံး စစ်
            foreach ($order->items as $item) {
                $stock = (float) $item->product->stock;
                if ((float) $item->qty_base > $stock + 0.0001) {
                    throw ValidationException::withMessages([
                        'stock' => "လက်ကျန် မလုံလောက်ပါ — {$item->product->displayName()} (လက်ကျန် " . qty_fmt($stock) . " {$item->product->base_unit})။",
                    ]);
                }
            }

            $sale = Sale::create([
                'invoice_no'    => $this->nextInvoiceNo(),
                'sold_at'       => now(),
                'user_id'       => $request->user()->id,
                'customer_id'   => $order->customer_id,
                'customer_name' => $order->customer_name,
                'subtotal'      => $order->subtotal,
                'discount'      => $order->discount,
                'total'         => $order->total,
                'paid_amount'   => $order->total,
                'change_amount' => 0,
                'note'          => "Order {$order->order_no}",
            ]);

            $totalCost = 0;
            foreach ($order->items as $item) {
                $costBase = $this->inventory->issueStock(
                    $item->product, (float) $item->qty_base,
                    $request->user()->id, $sale, "Order {$order->order_no}"
                );
                $lineCost = (float) $item->qty_base * $costBase;

                $sale->items()->create([
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'unit_label'      => $item->unit_label,
                    'factor'          => $item->factor,
                    'qty'             => $item->qty,
                    'qty_base'        => $item->qty_base,
                    'unit_price'      => $item->unit_price,
                    'line_total'      => $item->line_total,
                    'unit_cost_base'  => $costBase,
                    'line_cost'       => $lineCost,
                ]);
                $totalCost += $lineCost;
            }

            $sale->update([
                'total_cost' => $totalCost,
                'profit'     => (float) $order->total - $totalCost,
            ]);

            $order->update([
                'status'        => Order::STATUS_DELIVERED,
                'delivery_date' => now()->toDateString(),
                'delivered_by'  => $request->user()->id,
                'sale_id'       => $sale->id,
            ]);
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'ပေးပို့ပြီး — အရောင်းအဖြစ် မှတ်တမ်းတင်ပြီးပါပြီ။');
    }

    public function cancel(Order $order)
    {
        if (! $order->isPending()) {
            return back()->with('error', 'ဤ order ကို ပြောင်းလဲ၍ မရတော့ပါ။');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return back()->with('success', 'Order ပယ်ဖျက်ပြီးပါပြီ။');
    }

    public function destroy(Order $order)
    {
        if ($order->status === Order::STATUS_DELIVERED) {
            return back()->with('error', 'ပေးပို့ပြီး order ကို ဖျက်၍မရပါ။');
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', __('app.deleted'));
    }

    private function nextNo(): string
    {
        $prefix = 'ORD-' . now()->format('ymd') . '-';
        $last = Order::where('order_no', 'like', $prefix . '%')->orderByDesc('order_no')->value('order_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function nextInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('ymd') . '-';
        $last = Sale::where('invoice_no', 'like', $prefix . '%')->orderByDesc('invoice_no')->value('invoice_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
