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

    public function show($slug)
    {
        // Cari produk berdasarkan kolom slug, bukan ID
        // Tambahkan with('images') agar galeri foto ikut terbawa ke frontend
        $product = Product::with('images')->where('slug', $slug)->first();
    
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function store(Request $request)
    {
        $mainImage = null;
        $allImages = [];
    
        // 1. Ambil dan simpan gambar fisiknya dulu
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $filename = time() . '_' . Str::random(5) . '_' . $file->getClientOriginalName();
                $file->storeAs('products', $filename, 'public');
                
                $allImages[] = $filename; // Simpan untuk tabel galeri nanti
    
                // Tentukan gambar pertama sebagai cover utama
                if ($index == 0) {
                    $mainImage = $filename;
                }
            }
        }
    
        // 2. Simpan produk utama (Sekarang variabel $mainImage sudah punya nilai)
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'image'       => $mainImage, // Langsung isi bareng nama & harga
            'thumbnail'   => $mainImage, // Langsung isi bareng nama & harga
            'disc'       => $request->disc,                       
        ]);
    
        // 3. Simpan ke tabel galeri product_images
        foreach ($allImages as $imageName) {
            ProductImage::create([
                'product_id' => $product->id,
                'image'      => $imageName
            ]);
        }
    
        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }
    
        return response()->json([
            "message" => "Berhasil! Cek kolom image di database.",
            "data"    => $product->load('images')
        ], 201);
    }
    
    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);
    
    // 1. Update data teks dasar
    $product->update([
        'name'        => $request->name,
        'slug'        => $request->slug ?? Str::slug($request->name),
        'description' => $request->description,
        'price'       => $request->price,
        'stock'       => $request->stock,
        'disc'        => $request->disc,             
    ]);

    // 2. Jika ada upload foto baru
    if ($request->hasFile('images')) {
        $newImages = [];
        foreach ($request->file('images') as $index => $file) {
            $filename = time() . '_' . Str::random(5) . '_' . $file->getClientOriginalName();
            $file->storeAs('products', $filename, 'public');
            
            // Simpan ke tabel product_images
            ProductImage::create([
                'product_id' => $product->id,
                'image'      => $filename
            ]);

            $newImages[] = $filename;
        }

        // OTOMATIS: Jadikan foto pertama dari upload baru sebagai cover utama
        $product->update([
            'image'     => $newImages[0],
            'thumbnail' => $newImages[0]
        ]);
    }

    // 3. Sinkronisasi Kategori
    if ($request->categories) {
        $product->categories()->sync($request->categories);
    }

    return response()->json([
        "message" => "Update berhasil!",
        "data"    => $product->fresh()->load('images')
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
