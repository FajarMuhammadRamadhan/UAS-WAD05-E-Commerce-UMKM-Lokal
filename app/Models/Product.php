<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id', // Ubah dari 'category' menjadi 'category_id'
        'price',
        'stock',
        'description',
        'image',
    ];

    /**
     * Relasi: Satu produk terhubung ke satu kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Accessor untuk memastikan relasi category selalu diakses dengan benar
     */
    public function getCategoryAttribute()
    {
        return $this->getRelationValue('category');
    }
}