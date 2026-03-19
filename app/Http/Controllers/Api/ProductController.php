<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Log 1: Memastikan fungsi terpanggil
            \Log::info('Step 1: Masuk ke fungsi index');
    
            $query = Product::query();
    
            // Log 2: Cek apakah relasi images bermasalah
            \Log::info('Step 2: Mencoba memuat relasi images');
            $query->with(['images']);
    
            $products = $query->latest()->paginate(12);
    
            // Log 3: Berhasil
            \Log::info('Step 3: Data berhasil ditarik');
            return response()->json($products);
    
        } catch (\Exception $e) {
            // Log Error: Mencatat pesan error ke log Railway
            \Log::error('ERROR DI PRODUCTS: ' . $e->getMessage());
    
            return response()->json([
                'debug_error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function show($id) // Atau fungsi yang kamu gunakan untuk mengambil data edit
    {
        // Gunakan 'with' untuk mengambil relasi images sekalian
        $product = Product::with('images')->find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }
    
        // Ubah format data gambar agar lebih mudah dipakai di Frontend
        $product->images->each(function($image) {
            // Tambahkan URL lengkap untuk setiap gambar
            $image->url = asset('storage/products/' . $image->image);
        });
    
        return response()->json([
            'data' => $product
        ]);
    }

    public function store(Request $request)
    {
        // 1. Buat produk dasar terlebih dahulu
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'image'       => null,
            'thumbnail'   => null,
        ]);
    
        // 2. Proses upload gambar jika ada
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                // Buat nama file unik
                $filename = time() . '_' . Str::random(5) . '_' . $file->getClientOriginalName();
                
                // Simpan file ke storage public/products
                $file->storeAs('products', $filename, 'public');
        
                // Simpan ke tabel relasi product_images
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $filename
                ]);
        
                // JIKA ini adalah file pertama (index 0), LANGSUNG update tabel products
                if ($index == 0) {
                    $product->update([
                        'image'     => $filename,
                        'thumbnail' => $filename
                    ]);
                }
            }
        }
    
        // 3. Sinkronisasi kategori
        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }
    
        // Load ulang data terbaru untuk dikirim ke frontend
        return response()->json([
            "message" => "Product created and thumbnail set",
            "data"    => $product->fresh()->load('images') 
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(["message" => "Product not found"], 404);
        }

        $product->update([
            'name'        => $request->name ?? $product->name,
            'slug'        => $request->slug ?? $product->slug,
            'description' => $request->description ?? $product->description,
            'price'       => $request->price ?? $product->price,
            'stock'       => $request->stock ?? $product->stock,
        ]);

        return response()->json([
            "message" => "Product updated",
            "data"    => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(["message" => "Product not found"], 404);
        }

        $product->delete();

        return response()->json(["message" => "Product deleted"]);
    }

    public function deleteImage($id)
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return response()->json(["message" => "Image not found"], 404);
        }

        $image->delete();

        return response()->json(["message" => "Image deleted"]);
    }

    public function destroyImage($id)
    {
        // Cari gambar tunggal
        $image = ProductImage::find($id);
    
        if (!$image) {
            return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
        }
    
        // Hapus file fisik dari folder storage
        if ($image->image && Storage::disk('public')->exists('products/' . $image->image)) {
            Storage::disk('public')->delete('products/' . $image->image);
        }
    
        // Hapus data dari database
        $image->delete();
    
        return response()->json([
            'message' => 'Gambar berhasil dihapus'
        ]);
    }
    
    public function upload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['message' => 'No image uploaded'], 400);
        }

        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('products', $filename, 'public');

        return response()->json(['url' => $filename]);
    }
}
