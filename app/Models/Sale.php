<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no', 'sold_at', 'user_id', 'customer_id', 'customer_name',
        'subtotal', 'discount', 'total', 'paid_amount', 'change_amount',
        'total_cost', 'profit', 'note',
    ];

    protected $casts = [
        'sold_at'       => 'datetime',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'total'         => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'change_amount' => 'decimal:2',
        'total_cost'    => 'decimal:2',
        'profit'        => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
