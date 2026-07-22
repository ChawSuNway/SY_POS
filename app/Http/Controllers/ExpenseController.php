<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

/** အထွေထွေ အသုံးစရိတ် — လအလိုက် မှတ်တမ်း/စာရင်း */
class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }
        [$year, $mon] = explode('-', $month);

        $base = Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $mon);

        $expenses = (clone $base)
            ->with('user')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($s) => $s
                ->where('category', 'like', "%{$request->q}%")
                ->orWhere('note', 'like', "%{$request->q}%")))
            ->latest('expense_date')->latest('id')
            ->paginate(20)->withQueryString();

        $monthTotal = (clone $base)->sum('amount');

        $byCategory = (clone $base)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('category')->orderByDesc('total')->get();

        // datalist အကြံပြု — default + ရှိပြီးသား
        $categories = collect(Expense::DEFAULT_CATEGORIES)
            ->merge(Expense::distinct()->pluck('category'))
            ->unique()->values();

        return view('expenses.index', compact('expenses', 'month', 'monthTotal', 'byCategory', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_date' => ['required', 'date'],
            'category'     => ['required', 'string', 'max:100'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'note'         => ['nullable', 'string', 'max:200'],
        ]);

        Expense::create($data + ['user_id' => $request->user()->id]);

        return back()->with('success', __('app.saved'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', __('app.deleted'));
    }
}
