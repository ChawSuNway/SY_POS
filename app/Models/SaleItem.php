<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'product_unit_id', 'unit_label',
        'factor', 'qty', 'qty_base', 'unit_price', 'line_total',
        'unit_cost_base', 'line_cost',
    ];

    protected $casts = [
        'factor'         => 'decimal:4',
        'qty'            => 'decimal:3',
        'qty_base'       => 'decimal:3',
        'unit_price'     => 'decimal:2',
        'line_total'     => 'decimal:2',
        'unit_cost_base' => 'decimal:4',
        'line_cost'      => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lineProfit(): float
    {
        return (float) $this->line_total - (float) $this->line_cost;
    }
}
