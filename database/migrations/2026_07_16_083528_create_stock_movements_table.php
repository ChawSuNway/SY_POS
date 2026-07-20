<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['purchase', 'sale', 'adjustment']);
            $table->decimal('qty_base', 15, 3);             // +ဝင် / -ထွက် (base unit)
            $table->decimal('balance_after', 15, 3);        // လက်ကျန် (movement ပြီးနောက်)
            $table->nullableMorphs('reference');            // reference_type + reference_id
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
