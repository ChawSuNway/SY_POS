<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('type')->orderBy('name')->get()->groupBy('type');

        return view('brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        Brand::create($this->validateData($request));

        return back()->with('success', __('app.saved'));
    }

    public function update(Request $request, Brand $brand)
    {
        $brand->update($this->validateData($request, $brand->id));

        return back()->with('success', __('app.saved'));
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'ဤတံဆိပ်တွင် ကုန်ပစ္စည်းများ ရှိနေသဖြင့် ဖျက်၍မရပါ။');
        }

        $brand->delete();

        return back()->with('success', __('app.deleted'));
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['rice', 'oil'])],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('brands')->where(fn ($q) => $q->where('type', $request->type))->ignore($ignoreId),
            ],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
