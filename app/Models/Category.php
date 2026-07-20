<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

class Category extends Model
{
    use BelongsToShop;

    protected $fillable = ['type', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
