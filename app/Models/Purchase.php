<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

class Purchase extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'purchase_no', 'purchase_date', 'user_id', 'supplier_id',
        'supplier_name', 'total_cost', 'paid_amount', 'credit_due', 'note',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_cost'    => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'credit_due'    => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class)->orderByDesc('paid_at');
    }
}
