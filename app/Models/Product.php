<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

class Product extends Model
{
    use BelongsToShop;

    public const TYPE_RICE = 'rice';
    public const TYPE_OIL  = 'oil';

    protected $fillable = [
        'type', 'category_id', 'brand_id', 'name',
        'base_unit', 'stock', 'avg_cost', 'low_stock_threshold', 'is_active',
    ];

    protected $casts = [
        'stock'               => 'decimal:3',
        'avg_cost'            => 'decimal:4',
        'low_stock_threshold' => 'decimal:3',
        'is_active'           => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class)->orderBy('sort_order');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /** ဆန် / ဆီ label */
    public function typeLabel(): string
    {
        return $this->type === self::TYPE_RICE ? 'ဆန် (Rice)' : 'ဆီ (Oil)';
    }

    /** category + brand ကို ပေါင်းပြီး display name */
    public function displayName(): string
    {
        $parts = array_filter([
            optional($this->category)->fullName(),
            optional($this->brand)->name,
        ]);
        $base = implode(' - ', $parts);

        return $this->name ? "{$base} ({$this->name})" : $base;
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold > 0
            && $this->stock <= $this->low_stock_threshold;
    }

    /** လက်ကျန်တန်ဖိုး (weighted-avg) */
    public function stockValue(): float
    {
        return (float) $this->stock * (float) $this->avg_cost;
    }
}
