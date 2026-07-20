<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CASHIER     = 'cashier';
    public const ROLE_MANAGER     = 'manager';
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'shop_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    /** super_admin > admin > manager > cashier ranking. */
    public function hasAtLeast(string $role): bool
    {
        $rank = [
            self::ROLE_CASHIER     => 1,
            self::ROLE_MANAGER     => 2,
            self::ROLE_ADMIN       => 3,
            self::ROLE_SUPER_ADMIN => 4,
        ];

        return ($rank[$this->role] ?? 0) >= ($rank[$role] ?? 99);
    }

    public function roleLabel(): string
    {
        return [
            self::ROLE_CASHIER     => 'Cashier / ကက်ရှီယာ',
            self::ROLE_MANAGER     => 'Manager / မန်နေဂျာ',
            self::ROLE_ADMIN       => 'Admin / အက်ဒမင်',
            self::ROLE_SUPER_ADMIN => 'Super Admin',
        ][$this->role] ?? $this->role;
    }
}
