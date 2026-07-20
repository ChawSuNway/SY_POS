<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchases')->withSum('purchases', 'total_cost');

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($this->validateData($request));

        return redirect()->route('suppliers.index')->with('success', __('app.saved'));
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->with('user')->latest('purchase_date')->latest('id')->paginate(20);

        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validateData($request));

        return redirect()->route('suppliers.index')->with('success', __('app.saved'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return back()->with('success', __('app.deleted'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'note'    => ['nullable', 'string', 'max:500'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
