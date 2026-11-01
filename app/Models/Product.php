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

    protected $appends = [
        'image_url',
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

    /**
     * Dapatkan URL gambar produk (gambar upload atau foto default berkualitas tinggi)
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }
            if (str_starts_with($this->image, 'uploads/')) {
                return asset($this->image);
            }
            if (file_exists(public_path('uploads/' . $this->image))) {
                return asset('uploads/' . $this->image);
            }
            if (file_exists(public_path('storage/' . $this->image))) {
                return asset('storage/' . $this->image);
            }
            return asset('storage/' . $this->image);
        }

        return $this->getDefaultImage();
    }

    /**
     * Mendapatkan URL foto default relevan berdasarkan nama produk & kategori
     */
    public function getDefaultImage(): string
    {
        $name = strtolower($this->name ?? '');
        $category = strtolower($this->category->name ?? '');

        // 1. Minyak Goreng / Minyak Sawit / Margarin
        if (str_contains($name, 'minyak') || str_contains($name, 'goreng') || str_contains($name, 'mentega') || str_contains($name, 'margarin') || str_contains($name, 'sawit') || str_contains($name, 'bimoli') || str_contains($name, 'filma') || str_contains($name, 'sunco')) {
            return 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&q=80';
        }

        // 2. Beras
        if (str_contains($name, 'beras') || str_contains($name, 'nasi') || str_contains($name, 'pandan')) {
            return 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80';
        }

        // 3. Telur
        if (str_contains($name, 'telur') || str_contains($name, 'egg')) {
            return 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?auto=format&fit=crop&w=400&q=80';
        }

        // 4. Mie / Indomie / Sedap / Bihun
        if (str_contains($name, 'mie') || str_contains($name, 'mi ') || str_contains($name, 'noodle') || str_contains($name, 'bihun') || str_contains($name, 'indomie') || str_contains($name, 'sedap') || str_contains($name, 'sedaap')) {
            return 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=400&q=80';
        }

        // 5. Kopi / Teh / Gula
        if (str_contains($name, 'kopi') || str_contains($name, 'coffee') || str_contains($name, 'teh') || str_contains($name, 'gula')) {
            return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80';
        }

        // 6. Minuman / Soda / Cola / Susu / Air Mineral
        if (str_contains($name, 'cola') || str_contains($name, 'susu') || str_contains($name, 'aqua') || str_contains($name, 'mineral') || str_contains($name, 'sirup') || str_contains($name, 'jus') || str_contains($name, 'fanta') || str_contains($name, 'sprite') || str_contains($category, 'minuman')) {
            return 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=400&q=80';
        }

        // 7. Makanan Ringan / Snack / Ciki / Biskuit
        if (str_contains($name, 'ciki') || str_contains($name, 'snack') || str_contains($name, 'keripik') || str_contains($name, 'biskuit') || str_contains($name, 'wafer') || str_contains($name, 'citatos') || str_contains($name, 'taro') || str_contains($category, 'makanan')) {
            return 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=400&q=80';
        }

        // 8. Sabun / Deterjen / Shampo / Perawatan Tubuh / Rumah Tangga
        if (str_contains($name, 'sabun') || str_contains($name, 'rinso') || str_contains($name, 'deterjen') || str_contains($name, 'shampoo') || str_contains($name, 'pasta') || str_contains($name, 'pepsodent') || str_contains($name, 'lifebuoy') || str_contains($category, 'perawatan') || str_contains($category, 'rumah tangga')) {
            return 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=400&q=80';
        }

        // 9. Sembako / Default Umum
        return 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=400&q=80';
    }
}