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
            // 1. Coba ambil produk TANPA relasi categories dulu
            // Banyak error 500 terjadi karena tabel 'categories' belum dibuat/di-migrate
            $query = Product::with(['images']); 
    
            if ($request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
    
            // 2. Gunakan get() dulu untuk memastikan data keluar
            $products = $query->latest()->paginate(12);
    
            return response()->json($products);
    
        } catch (\Exception $e) {
            // 3. JIKA ERROR, kode ini akan menampilkan pesan error aslinya di browser
            // Jadi kamu tidak akan melihat angka 500 lagi, tapi pesan error detilnya
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => 'Cek apakah tabel products dan product_images sudah di-migrate di database'
            ], 500);
        }
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->with(['images', 'categories'])->first();
        
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock
        ]);

        $thumbnail = null;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('products', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $filename
                ]);

                if ($index == 0) {
                    $thumbnail = $filename;
                }
            }

            $product->update(['thumbnail' => $thumbnail]);
        }

        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }

        return response()->json([
            "message" => "Product created",
            "data"    => $product->load('images')
        ]);
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
