<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'type', 'qty_base', 'balance_after',
        'reference_type', 'reference_id', 'user_id', 'note',
    ];

    protected $casts = [
        'qty_base'      => 'decimal:3',
        'balance_after' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
