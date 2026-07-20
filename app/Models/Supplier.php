<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'note', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /** စုစုပေါင်း ဝယ်ယူခဲ့သည့် ကုန်ကျစရိတ် */
    public function totalPurchased(): float
    {
        return (float) $this->purchases()->sum('total_cost');
    }

    public function purchasesCount(): int
    {
        return $this->purchases()->count();
    }
}
