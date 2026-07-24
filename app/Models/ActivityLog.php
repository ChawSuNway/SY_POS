<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * လုပ်ဆောင်ချက် မှတ်တမ်း။ Multi-tenant scope (BelongsToShop) မသုံး —
 * Super Admin က ဆိုင်အားလုံး၏ မှတ်တမ်းကို မြင်ရရန်။ created_at သာရှိ၊ updated_at မရှိ။
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'shop_id', 'action', 'method', 'path', 'subject_type', 'subject_id', 'ip',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
