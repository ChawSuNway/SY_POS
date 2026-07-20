<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id', 'label', 'factor', 'selling_price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'factor'        => 'decimal:4',
        'selling_price' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** e.g. "အိတ် (= ၄၀၀ ဗူး)" */
    public function labelWithFactor(): string
    {
        return "{$this->label} (= " . rtrim(rtrim(number_format((float) $this->factor, 3), '0'), '.') . ' ' . optional($this->product)->base_unit . ')';
    }
}
