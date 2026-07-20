<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToShop;

class Category extends Model
{
    use BelongsToShop;

    protected $fillable = ['type', 'name', 'parent_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /** ပင်မ အမျိုးအစား ဟုတ်မဟုတ် */
    public function isParent(): bool
    {
        return $this->parent_id === null;
    }

    /** "ပင်မ ▸ sub" ပုံစံ အမည် (sub ဖြစ်လျှင် parent ပါ တွဲပြ) */
    public function fullName(): string
    {
        return $this->parent_id
            ? optional($this->parent)->name.' ▸ '.$this->name
            : $this->name;
    }
}
