<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

/** ပျက်စီး/ဆုံးရှုံး မှတ်တမ်း */
class StockLoss extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'lost_at', 'product_id', 'unit_label', 'factor',
        'qty', 'qty_base', 'unit_cost_base', 'loss_value',
        'reason', 'user_id',
    ];

    protected $casts = [
        'lost_at'        => 'date',
        'factor'         => 'decimal:4',
        'qty'            => 'decimal:3',
        'qty_base'       => 'decimal:3',
        'unit_cost_base' => 'decimal:4',
        'loss_value'     => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
