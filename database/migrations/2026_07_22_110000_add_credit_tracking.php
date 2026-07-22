<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * အကြွေးစာရင်း — ရရန်ရှိ (ဖောက်သည် အကြွေးရောင်း) + ပေးရန်ရှိ (အ၀ယ် မကျေငွေ)။
 */
return new class extends Migration
{
    public function up(): void
    {
        // ရောင်းချမှု — ကျန်အကြွေး (total - paid)
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('credit_due', 15, 2)->default(0)->after('change_amount');
        });

        // အ၀ယ် — ပေးချေပြီးငွေ + ကျန်အကြွေး
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_cost');
            $table->decimal('credit_due', 15, 2)->default(0)->after('paid_amount');
        });
        // ရှိပြီးသား အ၀ယ်များ — အပြည့်ချေပြီးဟု သတ်မှတ်
        DB::table('purchases')->update(['paid_amount' => DB::raw('total_cost')]);

        // အကြွေးဆပ်/ချေ မှတ်တမ်းများ
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained();
            $table->enum('kind', ['receivable', 'payable']);   // ရရန် / ပေးရန်
            $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index(['kind', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'credit_due']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('credit_due');
        });
    }
};
