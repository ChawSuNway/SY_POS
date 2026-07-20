<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_unit_id')->nullable()->constrained();
            $table->string('unit_label');                   // ရောင်းသည့် ယူနစ်
            $table->decimal('factor', 15, 4);               // base-unit / ယူနစ်
            $table->decimal('qty', 15, 3);                  // ယူနစ်အရေအတွက်
            $table->decimal('qty_base', 15, 3);             // = qty * factor
            $table->decimal('unit_price', 15, 2);           // ရောင်းစျေး / ယူနစ်
            $table->decimal('line_total', 15, 2);           // = qty * unit_price
            $table->decimal('unit_cost_base', 15, 4);       // sale ချိန် avg cost / base unit
            $table->decimal('line_cost', 15, 2);            // = qty_base * unit_cost_base
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
