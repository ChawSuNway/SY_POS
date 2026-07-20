<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('sales')->withSum('sales', 'total');

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        Customer::create($this->validateData($request));

        return redirect()->route('customers.index')->with('success', __('app.saved'));
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->with('user')->latest('sold_at')->paginate(20);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validateData($request));

        return redirect()->route('customers.index')->with('success', __('app.saved'));
    }

    public function destroy(Customer $customer)
    {
        // sales.customer_id က nullOnDelete — မှတ်တမ်း မပျက်ဘဲ ဖောက်သည်ကိုသာ ဖျက်
        $customer->delete();

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
