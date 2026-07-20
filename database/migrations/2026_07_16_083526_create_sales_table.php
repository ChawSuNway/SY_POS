<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();           // ဘောက်ချာနံပါတ်
            $table->dateTime('sold_at');
            $table->foreignId('user_id')->constrained();      // cashier
            $table->string('customer_name')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);      // = subtotal - discount
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0); // COGS (weighted avg) — အမြတ်တွက်ရန်
            $table->decimal('profit', 15, 2)->default(0);     // = total - total_cost
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
