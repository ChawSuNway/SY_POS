<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Users / Roles ----------
        User::updateOrCreate(['email' => 'admin@shweyee.test'], [
            'name' => 'Admin (ဦးစိုးဝင်း)', 'role' => 'admin',
            'password' => 'password', 'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'manager@shweyee.test'], [
            'name' => 'Manager (ဒေါ်မြင့်မြင့်)', 'role' => 'manager',
            'password' => 'password', 'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'cashier@shweyee.test'], [
            'name' => 'Cashier (မသီတာ)', 'role' => 'cashier',
            'password' => 'password', 'is_active' => true,
        ]);

        $admin = User::where('email', 'admin@shweyee.test')->first();
        $inventory = app(InventoryService::class);

        // ---------- Customers (ဖောက်သည်) ----------
        foreach ([
            ['ဦးဘချစ် (ဆိုင်)', '09-450012345', 'အမှတ် ၂၃၊ ဗိုလ်ချုပ်လမ်း'],
            ['ဒေါ်စန်းစန်း', '09-771122334', 'ရွာမ ဈေးတန်း'],
            ['မောင်မောင် လက်ကား', '09-988776655', 'အောက်လမ်း'],
            ['ဟိုတယ် ရွှေပြည်', '01-234567', 'မြို့လယ်'],
        ] as $c) {
            Customer::firstOrCreate(['name' => $c[0]], ['phone' => $c[1], 'address' => $c[2]]);
        }

        // ---------- Suppliers (ပေးသွင်းသူ) ----------
        foreach ([
            ['ရွှေဘိုဆန်ကုန်စည်', '09-402223344', 'ရွှေဘို'],
            ['မန္တလေး ဆီလက်ကား', '09-911223344', 'မန္တလေး ၈၄လမ်း'],
            ['ဧရာဝတီ ဆန်စက်', '09-255667788', 'ပုသိမ်'],
        ] as $s) {
            Supplier::firstOrCreate(['name' => $s[0]], ['phone' => $s[1], 'address' => $s[2]]);
        }

        // ---------- Categories ----------
        $riceCats = ['ပေါ်ဆန်း', 'ဇီယာ', 'ဧည့်မထ', 'မနော်သုခ', 'ငစိန်'];
        $oilCats  = ['ပဲဆီ', 'နှမ်းဆီ', 'စားအုန်းဆီ', 'နေကြာဆီ'];
        foreach ($riceCats as $c) Category::firstOrCreate(['type' => 'rice', 'name' => $c]);
        foreach ($oilCats as $c)  Category::firstOrCreate(['type' => 'oil', 'name' => $c]);

        // ---------- Brands ----------
        $riceBrands = ['ရွှေတောင်', 'ရွှေဝါ', 'အာရုံသစ်', 'ဟသ်တာ'];
        $oilBrands  = ['ရွှေအိုး', 'ပြည်တော်သာ', 'မေဓာဝီ'];
        foreach ($riceBrands as $b) Brand::firstOrCreate(['type' => 'rice', 'name' => $b]);
        foreach ($oilBrands as $b)  Brand::firstOrCreate(['type' => 'oil', 'name' => $b]);

        // ---------- Rice products ----------
        // အခြေခံယူနစ် = ဗူး ။ ၁ ပြည် = ၈ ဗူး ၊ ၁ အိတ် = ၄၈ ပြည် = ၃၈၄ ဗူး (per-product ချိန်ညှိနိုင်)
        $this->makeRice($inventory, $admin, 'ပေါ်ဆန်း', 'ရွှေတောင်', bagPyi:48, prices:[
            'အိတ်' => 130000, 'ပြည်' => 3000, 'ဗူး' => 400,
        ], openingBags:20, unitCostBase:330);

        $this->makeRice($inventory, $admin, 'ဇီယာ', 'ရွှေဝါ', bagPyi:48, prices:[
            'အိတ်' => 98000, 'ပြည်' => 2200, 'ဗူး' => 300,
        ], openingBags:30, unitCostBase:250);

        $this->makeRice($inventory, $admin, 'ဧည့်မထ', 'အာရုံသစ်', bagPyi:48, prices:[
            'အိတ်' => 115000, 'ပြည်' => 2600, 'ဗူး' => 350,
        ], openingBags:15, unitCostBase:290);

        // ---------- Oil products ----------
        // အခြေခံယူနစ် = ဆယ်သား ။ ၁ ပိဿာ = ၁၀ ဆယ်သား ၊ ၁ ပုံး = ၁၀၀ ဆယ်သား (=၁၀ ပိဿာ)
        $this->makeOil($inventory, $admin, 'ပဲဆီ', 'ရွှေအိုး', prices:[
            'ပုံး (၁၀ ပိဿာ)' => 105000, 'ပိဿာ' => 11000, 'ဆယ်သား' => 1150,
        ], openingTins:12, unitCostBase:980);

        $this->makeOil($inventory, $admin, 'စားအုန်းဆီ', 'ပြည်တော်သာ', prices:[
            'ပုံး (၁၀ ပိဿာ)' => 62000, 'ပိဿာ' => 6500, 'ဆယ်သား' => 680,
        ], openingTins:20, unitCostBase:600);

        $this->makeOil($inventory, $admin, 'နှမ်းဆီ', 'မေဓာဝီ', prices:[
            'ပုံး (၁၀ ပိဿာ)' => 145000, 'ပိဿာ' => 15000, 'ဆယ်သား' => 1550,
        ], openingTins:8, unitCostBase:1350);
    }

    private function makeRice(InventoryService $inv, User $admin, string $cat, string $brand, int $bagPyi, array $prices, int $openingBags, float $unitCostBase): void
    {
        $category = Category::firstOrCreate(['type' => 'rice', 'name' => $cat]);
        $brandM = Brand::firstOrCreate(['type' => 'rice', 'name' => $brand]);

        $product = Product::firstOrCreate(
            ['type' => 'rice', 'category_id' => $category->id, 'brand_id' => $brandM->id],
            ['base_unit' => 'ဗူး', 'low_stock_threshold' => 800] // ဗူး (~2 အိတ်)
        );

        $bagFactor = $bagPyi * 8; // ဗူး / အိတ်
        $unitFactors = ['အိတ်' => $bagFactor, 'ပြည်' => 8, 'ဗူး' => 1];
        $sort = 0;
        foreach ($prices as $label => $price) {
            $product->units()->firstOrCreate(['label' => $label], [
                'factor' => $unitFactors[$label] ?? 1,
                'selling_price' => $price,
                'sort_order' => $sort++,
            ]);
        }

        if ($openingBags > 0 && $product->stock == 0) {
            $inv->receiveStock($product, $openingBags * $bagFactor, $unitCostBase, $admin->id, null, 'Opening stock');
        }
    }

    private function makeOil(InventoryService $inv, User $admin, string $cat, string $brand, array $prices, int $openingTins, float $unitCostBase): void
    {
        $category = Category::firstOrCreate(['type' => 'oil', 'name' => $cat]);
        $brandM = Brand::firstOrCreate(['type' => 'oil', 'name' => $brand]);

        $product = Product::firstOrCreate(
            ['type' => 'oil', 'category_id' => $category->id, 'brand_id' => $brandM->id],
            ['base_unit' => 'ဆယ်သား', 'low_stock_threshold' => 200] // ဆယ်သား (~2 ပုံး)
        );

        // ၁ ပုံး = ၁၀၀ ဆယ်သား ၊ ၁ ပိဿာ = ၁၀ ဆယ်သား ၊ ၁ ဆယ်သား = ၁
        $unitFactors = ['ပုံး (၁၀ ပိဿာ)' => 100, 'ပိဿာ' => 10, 'ဆယ်သား' => 1];
        $sort = 0;
        foreach ($prices as $label => $price) {
            $product->units()->firstOrCreate(['label' => $label], [
                'factor' => $unitFactors[$label] ?? 1,
                'selling_price' => $price,
                'sort_order' => $sort++,
            ]);
        }

        if ($openingTins > 0 && $product->stock == 0) {
            $inv->receiveStock($product, $openingTins * 100, $unitCostBase, $admin->id, null, 'Opening stock');
        }
    }
}
