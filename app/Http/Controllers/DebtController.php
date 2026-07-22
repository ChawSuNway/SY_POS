<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * အကြွေးစာရင်း — ရရန်ရှိ (ဖောက်သည်) / ပေးရန်ရှိ (ပေးသွင်းသူ)။
 */
class DebtController extends Controller
{
    /** ရရန်ရှိ အကြွေး — အကြွေးကျန် ရောင်းချမှုများ (ဖောက်သည်အလိုက်) */
    public function receivable(Request $request)
    {
        $query = Sale::with('customer')->where('credit_due', '>', 0)->latest('sold_at');

        if ($request->filled('q')) {
            $query->where(fn ($s) => $s
                ->where('invoice_no', 'like', "%{$request->q}%")
                ->orWhere('customer_name', 'like', "%{$request->q}%"));
        }

        $sales = $query->paginate(20)->withQueryString();
        $totalDue = Sale::where('credit_due', '>', 0)->sum('credit_due');

        // ဖောက်သည်အလိုက် စုစုပေါင်း
        $byCustomer = Sale::where('credit_due', '>', 0)
            ->selectRaw('customer_id, customer_name, SUM(credit_due) as due, COUNT(*) as cnt')
            ->groupBy('customer_id', 'customer_name')
            ->orderByDesc('due')->get();

        $recentPayments = DebtPayment::with(['sale', 'user'])
            ->where('kind', DebtPayment::KIND_RECEIVABLE)
            ->latest('id')->limit(10)->get();

        return view('debts.receivable', compact('sales', 'totalDue', 'byCustomer', 'recentPayments'));
    }

    /** ဖောက်သည်ထံမှ အကြွေးငွေ လက်ခံ */
    public function payReceivable(Request $request, Sale $sale)
    {
        abort_unless((float) $sale->credit_due > 0, 404);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.(float) $sale->credit_due],
            'note'   => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $sale, $data) {
            DebtPayment::create([
                'kind'     => DebtPayment::KIND_RECEIVABLE,
                'sale_id'  => $sale->id,
                'amount'   => $data['amount'],
                'paid_at'  => now()->toDateString(),
                'note'     => $data['note'] ?? null,
                'user_id'  => $request->user()->id,
            ]);

            $sale->credit_due  = (float) $sale->credit_due - (float) $data['amount'];
            $sale->paid_amount = (float) $sale->paid_amount + (float) $data['amount'];
            $sale->save();
        });

        return back()->with('success', __('app.payment_recorded'));
    }

    /** ပေးရန်ရှိ အကြွေး — မကျေသေးသော အ၀ယ်များ (ပေးသွင်းသူအလိုက်) */
    public function payable(Request $request)
    {
        $query = Purchase::with('supplier')->where('credit_due', '>', 0)->latest('purchase_date');

        if ($request->filled('q')) {
            $query->where(fn ($s) => $s
                ->where('purchase_no', 'like', "%{$request->q}%")
                ->orWhere('supplier_name', 'like', "%{$request->q}%"));
        }

        $purchases = $query->paginate(20)->withQueryString();
        $totalDue = Purchase::where('credit_due', '>', 0)->sum('credit_due');

        $bySupplier = Purchase::where('credit_due', '>', 0)
            ->selectRaw('supplier_id, supplier_name, SUM(credit_due) as due, COUNT(*) as cnt')
            ->groupBy('supplier_id', 'supplier_name')
            ->orderByDesc('due')->get();

        $recentPayments = DebtPayment::with(['purchase', 'user'])
            ->where('kind', DebtPayment::KIND_PAYABLE)
            ->latest('id')->limit(10)->get();

        return view('debts.payable', compact('purchases', 'totalDue', 'bySupplier', 'recentPayments'));
    }

    /** ပေးသွင်းသူသို့ အကြွေးငွေ ပေးချေ */
    public function payPayable(Request $request, Purchase $purchase)
    {
        abort_unless((float) $purchase->credit_due > 0, 404);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.(float) $purchase->credit_due],
            'note'   => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $purchase, $data) {
            DebtPayment::create([
                'kind'        => DebtPayment::KIND_PAYABLE,
                'purchase_id' => $purchase->id,
                'amount'      => $data['amount'],
                'paid_at'     => now()->toDateString(),
                'note'        => $data['note'] ?? null,
                'user_id'     => $request->user()->id,
            ]);

            $purchase->credit_due  = (float) $purchase->credit_due - (float) $data['amount'];
            $purchase->paid_amount = (float) $purchase->paid_amount + (float) $data['amount'];
            $purchase->save();
        });

        return back()->with('success', __('app.payment_recorded'));
    }
}
