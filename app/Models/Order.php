<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'payment_method',
        'invoice_number',
        'total_price',
        'status',
        'cancellation_reason',
        'is_admin_read',
        'is_customer_read',
    ];

    protected $casts = [
        'is_admin_read' => 'boolean',
        'is_customer_read' => 'boolean',
    ];

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi',
            'paid' => 'Sudah Dibayar',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    // Relasi: Pesanan milik satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu pesanan memiliki banyak item pesanan
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}