<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Cek apakah user memiliki hak akses admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah kasir
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Cek apakah user adalah customer (pelanggan umum)
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer' || empty($this->role);
    }

    /**
     * Cek apakah user dapat mengelola transaksi penjualan (Admin & Kasir)
     */
    public function canManageOrders(): bool
    {
        return in_array($this->role, ['admin', 'kasir'], true);
    }

    /**
     * Mendapatkan label peran pengguna untuk tampilan antarmuka
     */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'kasir' => 'Kasir',
            default => 'Customer',
        };
    }

    /**
     * Relasi: Satu user memiliki banyak pesanan (Orders)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
