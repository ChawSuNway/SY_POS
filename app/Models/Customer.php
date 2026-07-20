<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'note', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /** စုစုပေါင်း ဝယ်ယူငွေ (ဤဖောက်သည်၏ အရောင်းစုစုပေါင်း) */
    public function totalSpent(): float
    {
        return (float) $this->sales()->sum('total');
    }

    public function salesCount(): int
    {
        return $this->sales()->count();
    }
}
