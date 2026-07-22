<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

/** အထွေထွေ အသုံးစရိတ် */
class Expense extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'expense_date', 'category', 'amount', 'note', 'user_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    /** အသုံးများသော အမျိုးအစား အကြံပြုချက်များ */
    public const DEFAULT_CATEGORIES = [
        '၀န်ထမ်းလစာ', 'အိတ်ဖိုး', 'သယ်ယူစရိတ်', 'ဆိုင်ခန်းခ', 'မီတာခ',
        'ရေဖိုး', 'စားသောက်စရိတ်', 'ပြင်ဆင်စရိတ်', 'အခြား',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
