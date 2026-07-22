<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ပျက်စီး/ဆုံးရှုံး မှတ်တမ်း (damage & loss write-offs)။
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained();
            $table->date('lost_at');                        // ပျက်စီးသည့်ရက်
            $table->foreignId('product_id')->constrained();
            $table->string('unit_label');                   // ရိုက်ထည့်သည့် ယူနစ်
            $table->decimal('factor', 12, 4)->default(1);   // base units per unit
            $table->decimal('qty', 12, 3);                  // ယူနစ်အလိုက် အရေအတွက်
            $table->decimal('qty_base', 15, 3);             // base unit အရေအတွက်
            $table->decimal('unit_cost_base', 15, 4);       // ဆုံးရှုံးချိန် avg cost (base)
            $table->decimal('loss_value', 15, 2);           // ဆုံးရှုံးတန်ဖိုး
            $table->string('reason');                       // အကြောင်းရင်း
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index('lost_at');
        });

        // stock movement type အသစ် 'loss'
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('purchase','sale','adjustment','opening','loss') NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_losses');
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('purchase','sale','adjustment','opening') NOT NULL");
    }
};
