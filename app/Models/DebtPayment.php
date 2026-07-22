<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

/** အကြွေးဆပ်/ချေ မှတ်တမ်း */
class DebtPayment extends Model
{
    use BelongsToShop;

    public const KIND_RECEIVABLE = 'receivable'; // ဖောက်သည်ထံမှ ရရန်
    public const KIND_PAYABLE    = 'payable';    // ပေးသွင်းသူသို့ ပေးရန်

    protected $fillable = [
        'kind', 'sale_id', 'purchase_id', 'amount', 'paid_at', 'note', 'user_id',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
