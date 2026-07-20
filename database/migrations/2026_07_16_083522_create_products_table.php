<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['rice', 'oil']);                 // ဆန် / ဆီ
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();                    // optional descriptive name
            $table->string('base_unit');                           // အသေးဆုံးယူနစ် — rice: ဗူး, oil: ဆယ်သား
            $table->decimal('stock', 15, 3)->default(0);           // လက်ကျန် (base unit ဖြင့်)
            $table->decimal('avg_cost', 15, 4)->default(0);        // weighted-average cost / base unit
            $table->decimal('low_stock_threshold', 15, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'category_id', 'brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
