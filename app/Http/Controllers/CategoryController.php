<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        // ပင်မ အမျိုးအစားများ (children ပါ) — type အလိုက် စု
        $parents = Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('type')->orderBy('name')
            ->get()->groupBy('type');

        return view('categories.index', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Category::create($data);

        return back()->with('success', __('app.saved'));
    }

    public function update(Request $request, Category $category)
    {
        // name + is_active သာ ပြင် (type / parent မပြောင်း)
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->where(fn ($q) => $q
                    ->where('shop_id', current_shop_id())
                    ->where('type', $category->type)
                    ->where('parent_id', $category->parent_id))->ignore($category->id),
            ],
            'is_active' => ['boolean'],
        ]);

        $category->update([
            'name'      => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('app.saved'));
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', __('app.category_has_children'));
        }
        if ($category->products()->exists()) {
            return back()->with('error', 'ဤအမျိုးအစားတွင် ကုန်ပစ္စည်းများ ရှိနေသဖြင့် ဖျက်၍မရပါ။');
        }

        $category->delete();

        return back()->with('success', __('app.deleted'));
    }

    private function validateData(Request $request): array
    {
        // sub-category ဖြစ်လျှင် parent ကို စစ် — type ကို parent မှ ယူ (၂ အဆင့်သာ)
        $parent = null;
        if ($request->filled('parent_id')) {
            $parent = Category::whereNull('parent_id')->find($request->parent_id);
            if (! $parent) {
                abort(422, 'Invalid parent category.');
            }
        }

        $type = $parent ? $parent->type : $request->type;

        $validated = $request->validate([
            'type' => [Rule::requiredIf(! $parent), Rule::in(['rice', 'oil'])],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->where(fn ($q) => $q
                    ->where('shop_id', current_shop_id())
                    ->where('type', $type)
                    ->where('parent_id', $parent?->id)),
            ],
        ]);

        return [
            'type'      => $type,
            'name'      => $validated['name'],
            'parent_id' => $parent?->id,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
