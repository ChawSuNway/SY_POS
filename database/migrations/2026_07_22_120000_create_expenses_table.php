<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * အထွေထွေ အသုံးစရိတ် — ၀န်ထမ်းလစာ / အိတ်ဖိုး / သယ်ယူစရိတ် / ဆိုင်ခန်းခ / မီတာခ စသည်။
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained();
            $table->date('expense_date');
            $table->string('category', 100);            // အမျိုးအစား (free text + suggestions)
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index(['shop_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
