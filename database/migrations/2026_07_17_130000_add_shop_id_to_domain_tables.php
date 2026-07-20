<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-tenant isolation — domain table အားလုံးတွင် shop_id ထည့်၊
 * ရှိပြီးသား row များ Shop #1 သို့ backfill၊ uniqueness များ ဆိုင်အလိုက် ပြင်။
 */
return new class extends Migration
{
    /** shop_id ထည့်ရန် table များ (ရိုးရိုး) */
    private array $simple = [
        'product_units', 'customers', 'suppliers', 'stock_movements',
    ];

    public function up(): void
    {
        $firstShopId = DB::table('shops')->min('id') ?? 1;

        // ---- shop_id column + backfill (ရိုးရိုး table များ) ----
        foreach ($this->simple as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->after('id')->constrained();
            });
            DB::table($t)->update(['shop_id' => $firstShopId]);
        }

        // ---- categories : unique(type,name) => unique(shop_id,type,name) ----
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('categories')->update(['shop_id' => $firstShopId]);
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_type_name_unique');
            $table->unique(['shop_id', 'type', 'name']);
        });

        // ---- brands : unique(type,name) => unique(shop_id,type,name) ----
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('brands')->update(['shop_id' => $firstShopId]);
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_type_name_unique');
            $table->unique(['shop_id', 'type', 'name']);
        });

        // ---- products : unique(type,category_id,brand_id) => +shop_id ----
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('products')->update(['shop_id' => $firstShopId]);
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_type_category_id_brand_id_unique');
            $table->unique(['shop_id', 'type', 'category_id', 'brand_id']);
        });

        // ---- orders : order_no unique => (shop_id, order_no) ----
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('orders')->update(['shop_id' => $firstShopId]);
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_no_unique');
            $table->unique(['shop_id', 'order_no']);
        });

        // ---- sales : invoice_no unique => (shop_id, invoice_no) ----
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('sales')->update(['shop_id' => $firstShopId]);
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_invoice_no_unique');
            $table->unique(['shop_id', 'invoice_no']);
        });

        // ---- purchases : purchase_no unique => (shop_id, purchase_no) ----
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained();
        });
        DB::table('purchases')->update(['shop_id' => $firstShopId]);
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_purchase_no_unique');
            $table->unique(['shop_id', 'purchase_no']);
        });
    }

    public function down(): void
    {
        // composite unique => original single/tuple unique ပြန်
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'type', 'name']);
            $table->unique(['type', 'name']);
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'type', 'name']);
            $table->unique(['type', 'name']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'type', 'category_id', 'brand_id']);
            $table->unique(['type', 'category_id', 'brand_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'order_no']);
            $table->unique('order_no');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'invoice_no']);
            $table->unique('invoice_no');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'purchase_no']);
            $table->unique('purchase_no');
        });

        foreach (['categories', 'brands', 'products', 'orders', 'sales', 'purchases', ...$this->simple] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropConstrainedForeignId('shop_id');
            });
        }
    }
};
