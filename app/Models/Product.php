<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        // 'image' dan 'thumbnail' tidak perlu ada di sini jika datanya di tabel lain
    ];

    // Menambahkan 'thumbnail_url' ke dalam output JSON secara otomatis
    protected $appends = ['thumbnail_url'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * ACCESSOR: Membuat kolom bayangan 'thumbnail_url'
     * Fungsi ini akan mengambil foto pertama dari tabel product_images
     */
    public function getThumbnailUrlAttribute()
    {
        // Ambil foto pertama dari relasi images
        $firstImage = $this->images()->first();

        if ($firstImage) {
            return asset('storage/products/' . $firstImage->image);
        }

        // Jika tidak ada foto, tampilkan placeholder agar tidak pecah
        return 'https://placehold.co/600x400?text=No+Image';
    }
}
