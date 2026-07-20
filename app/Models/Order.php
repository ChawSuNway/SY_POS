<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_no', 'order_date', 'delivery_date',
        'customer_id', 'customer_name', 'delivery_address', 'user_id', 'delivered_by',
        'status', 'subtotal', 'discount', 'total', 'sale_id', 'note',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'delivery_date' => 'date',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return [
            self::STATUS_PENDING   => 'မပေးပို့ရသေး / Pending',
            self::STATUS_DELIVERED => 'ပေးပို့ပြီး / Delivered',
            self::STATUS_CANCELLED => 'ပယ်ဖျက် / Cancelled',
        ][$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return [
            self::STATUS_PENDING   => 'amber',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_CANCELLED => 'gray',
        ][$this->status] ?? 'gray';
    }
}
