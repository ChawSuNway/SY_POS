<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name', 'name_en', 'tagline', 'tagline_en',
        'logo', 'phone', 'address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /** locale အလိုက် ဆိုင်နာမည် */
    public function displayName(): string
    {
        return (app()->getLocale() === 'en' && $this->name_en)
            ? $this->name_en
            : $this->name;
    }

    /** locale အလိုက် tagline */
    public function displayTagline(): ?string
    {
        return (app()->getLocale() === 'en' && $this->tagline_en)
            ? $this->tagline_en
            : $this->tagline;
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset($this->logo) : null;
    }
}
