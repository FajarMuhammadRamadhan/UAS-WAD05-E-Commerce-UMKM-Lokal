<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Relasi: Item ini bagian dari satu order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Item ini merujuk ke satu produk tertentu
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}