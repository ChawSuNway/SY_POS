<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('type')->orderBy('name')->get()->groupBy('type');

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Category::create($data);

        return back()->with('success', __('app.saved'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request, $category->id);
        $category->update($data);

        return back()->with('success', __('app.saved'));
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'ဤအမျိုးအစားတွင် ကုန်ပစ္စည်းများ ရှိနေသဖြင့် ဖျက်၍မရပါ။');
        }

        $category->delete();

        return back()->with('success', __('app.deleted'));
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['rice', 'oil'])],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->where(fn ($q) => $q->where('type', $request->type)->where('shop_id', current_shop_id()))->ignore($ignoreId),
            ],
            'is_active' => ['boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
