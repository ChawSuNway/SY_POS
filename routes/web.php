<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockLossController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Locale switch (guest ဖြစ်စေ login ဖြစ်စေ)
Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Guest
Route::get('login', [LoginController::class, 'show'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.attempt');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- POS / Sales : cashier နှင့် အထက် ----
    Route::middleware(['role:cashier', 'shop'])->group(function () {
        Route::get('pos', [SaleController::class, 'create'])->name('sales.create');
        Route::post('pos', [SaleController::class, 'store'])->name('sales.store');
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        // POS ajax: product ရွေးရင် units + price ဆွဲရန်
        Route::get('api/products/{product}/units', [SaleController::class, 'productUnits'])->name('api.product.units');

        // ဖောက်သည် မှတ်တမ်း — cashier ပါ စီမံနိုင် (POS တွင် အသုံးပြု)
        Route::resource('customers', CustomerController::class);

        // မှာယူမှု (Orders) — cashier ပါ စီမံနိုင်
        Route::resource('orders', OrderController::class)->except(['edit', 'update']);
        Route::post('orders/{order}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // ---- Purchases + Products + Reports : manager နှင့် အထက် ----
    Route::middleware(['role:manager', 'shop'])->group(function () {
        Route::resource('purchases', PurchaseController::class)->except(['edit', 'update']);

        Route::resource('suppliers', SupplierController::class);

        Route::resource('products', ProductController::class);

        // ဖွင့်လှစ်လက်ကျန် (Opening stock) — သီးသန့် screen
        Route::get('opening-stock', [OpeningStockController::class, 'index'])->name('opening-stock.index');
        Route::post('opening-stock', [OpeningStockController::class, 'store'])->name('opening-stock.store');

        // ပျက်စီး/ဆုံးရှုံး စာရင်း
        Route::get('losses', [StockLossController::class, 'index'])->name('losses.index');
        Route::get('losses/create', [StockLossController::class, 'create'])->name('losses.create');
        Route::post('losses', [StockLossController::class, 'store'])->name('losses.store');
        Route::delete('losses/{loss}', [StockLossController::class, 'destroy'])->name('losses.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low_stock');
    });

    // ---- Categories, Brands, Users : admin only ----
    Route::middleware(['role:admin', 'shop'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
        Route::resource('brands', BrandController::class)->except(['show', 'create', 'edit']);
        Route::resource('users', UserController::class)->except(['show']);

        // ဆိုင်အချက်အလက် / logo ဆက်တင် (မိမိဆိုင်)
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // ---- Super Admin only : ဆိုင်များ စီမံ ----
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('shops', ShopController::class)->except(['show']);
    });
});
