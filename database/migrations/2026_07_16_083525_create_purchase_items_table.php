<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_unit_id')->nullable()->constrained();
            $table->string('unit_label');                   // ၀ယ်ယူသည့် ယူနစ်
            $table->decimal('factor', 15, 4);               // base-unit / ယူနစ်
            $table->decimal('qty', 15, 3);                  // ယူနစ်အရေအတွက်
            $table->decimal('qty_base', 15, 3);             // = qty * factor
            $table->decimal('unit_cost', 15, 2);            // ၀ယ်စျေး / ယူနစ်
            $table->decimal('line_cost', 15, 2);            // = qty * unit_cost
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
