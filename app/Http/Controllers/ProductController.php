<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'units']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$term}%"));
            });
        }

        $products = $query->orderBy('type')->orderBy('category_id')->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create', $this->formData('rice'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        DB::transaction(function () use ($request, $data) {
            $product = Product::create([
                'type'                => $data['type'],
                'category_id'         => $data['category_id'],
                'brand_id'            => $data['brand_id'],
                'name'                => $data['name'] ?? null,
                'base_unit'           => $data['base_unit'],
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 0,
                'is_active'           => $request->boolean('is_active', true),
            ]);

            $this->syncUnits($product, $request);
        });

        return redirect()->route('products.index')->with('success', __('app.saved'));
    }

    public function edit(Product $product)
    {
        $product->load('units');

        return view('products.edit', $this->formData($product->type) + compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update([
                'type'                => $data['type'],
                'category_id'         => $data['category_id'],
                'brand_id'            => $data['brand_id'],
                'name'                => $data['name'] ?? null,
                'base_unit'           => $data['base_unit'],
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 0,
                'is_active'           => $request->boolean('is_active', true),
            ]);

            $this->syncUnits($product, $request);
        });

        return redirect()->route('products.index')->with('success', __('app.saved'));
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return back()->with('error', 'ရောင်း/ဝယ် မှတ်တမ်းရှိသဖြင့် ဖျက်၍မရပါ။ (ပိတ်ထားနိုင်သည်)');
        }

        $product->delete();

        return back()->with('success', __('app.deleted'));
    }

    private function formData(string $type): array
    {
        return [
            'categories' => Category::orderBy('type')->orderBy('name')->get(),
            'brands'     => Brand::orderBy('type')->orderBy('name')->get(),
            'canSetPrice'=> auth()->user()->isAdmin(),
        ];
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'type'        => ['required', Rule::in(['rice', 'oil'])],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id'    => [
                'required', 'exists:brands,id',
                // type + category + brand ပေါင်းစပ်မှု ထပ်နေခြင်း မဖြစ်စေရန်
                Rule::unique('products')->where(fn ($q) => $q
                    ->where('type', $request->type)
                    ->where('category_id', $request->category_id)
                    ->where('brand_id', $request->brand_id))->ignore($ignoreId),
            ],
            'name'        => ['nullable', 'string', 'max:150'],
            'base_unit'   => ['required', 'string', 'max:30'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'units'                => ['required', 'array', 'min:1'],
            'units.*.label'        => ['required', 'string', 'max:30'],
            'units.*.factor'       => ['required', 'numeric', 'min:0.0001'],
            'units.*.selling_price'=> ['nullable', 'numeric', 'min:0'],
            'units.*.id'           => ['nullable', 'integer'],
        ], [
            'brand_id.unique' => 'ဤ အမျိုးအမည် + အမျိုးအစား + တံဆိပ် ပေါင်းစပ်မှုဖြင့် ကုန်ပစ္စည်း ရှိပြီးသားဖြစ်သည်။',
        ], [
            'units.*.label'  => 'unit label',
            'units.*.factor' => 'unit factor',
        ]);
    }

    /** units array ကို sync — admin မဟုတ်လျှင် selling_price ကို မပြောင်း (Admin သတ်မှတ်ပိုင်ခွင့်) */
    private function syncUnits(Product $product, Request $request): void
    {
        $isAdmin = auth()->user()->isAdmin();
        $keepIds = [];
        $sort = 0;

        foreach ($request->input('units', []) as $row) {
            if (empty($row['label']) || ! isset($row['factor'])) {
                continue;
            }

            $existing = ! empty($row['id'])
                ? $product->units()->whereKey($row['id'])->first()
                : null;

            $attrs = [
                'label'      => $row['label'],
                'factor'     => $row['factor'],
                'is_active'  => (bool) ($row['is_active'] ?? true),
                'sort_order' => $sort++,
            ];

            // Admin သာ ရောင်းစျေး သတ်မှတ်/ပြင်နိုင်သည်
            if ($isAdmin) {
                $attrs['selling_price'] = $row['selling_price'] ?? 0;
            } elseif (! $existing) {
                $attrs['selling_price'] = 0;   // manager အသစ်ထည့် — Admin မှ စျေးထည့်ရန်
            }

            if ($existing) {
                $existing->update($attrs);
                $keepIds[] = $existing->id;
            } else {
                $unit = $product->units()->create($attrs);
                $keepIds[] = $unit->id;
            }
        }

        // ဖယ်ရှားထားသော units — ရောင်း/ဝယ်တွင် သုံးမထားလျှင်သာ ဖျက်
        $product->units()->whereNotIn('id', $keepIds)->each(function ($unit) {
            $used = $unit->product->saleItems()->where('product_unit_id', $unit->id)->exists()
                || $unit->product->purchaseItems()->where('product_unit_id', $unit->id)->exists();
            if ($used) {
                $unit->update(['is_active' => false]);
            } else {
                $unit->delete();
            }
        });
    }
}
