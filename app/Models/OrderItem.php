<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_unit_id', 'unit_label',
        'factor', 'qty', 'qty_base', 'unit_price', 'line_total',
    ];

    protected $casts = [
        'factor'     => 'decimal:4',
        'qty'        => 'decimal:3',
        'qty_base'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
