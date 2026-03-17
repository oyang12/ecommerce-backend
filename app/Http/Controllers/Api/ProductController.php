<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images','categories']);

        // =========================
        // SEARCH PRODUCT
        // =========================
        if ($request->search) {
            $query->where('name','like','%'.$request->search.'%');
        }

        // =========================
        // FILTER CATEGORY
        // =========================
        if ($request->category_id) {
            $query->whereHas('categories', function($q) use ($request){
                $q->where('categories.id', $request->category_id);
            });
        }

        // =========================
        // SORT PRODUCT
        // =========================
        if ($request->sort == 'price_asc') {
            $query->orderBy('price','asc');
        }

        elseif ($request->sort == 'price_desc') {
            $query->orderBy('price','desc');
        }

        elseif ($request->sort == 'newest') {
            $query->orderBy('created_at','desc');
        }

        else {
            $query->orderBy('id','desc');
        }

        // =========================
        // PAGINATION
        // =========================
        $products = $query->paginate(12);

        return response()->json($products);
    }

    public function show($slug)
    {
        return Product::where('slug',$slug)->with('images')->first();
    }

    public function store(Request $request)
    {

        $product = Product::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock
        ]);

        $thumbnail = null;

        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $index => $file) {

                $filename = time().'_'.$file->getClientOriginalName();

                $file->storeAs('products', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $filename
                ]);

                if($index == 0){
                    $thumbnail = $filename;
                }
            }

            $product->update([
                'thumbnail'=>$thumbnail
            ]);
        }

        if($request->categories)
        {
            $product->categories()->sync($request->categories);
        }

        return response()->json([
            "message" => "Product created",
            "data" => $product->load('images')
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if(!$product){
            return response()->json([
                "message" => "Product not found"
            ],404);
        }

        $filename = $product->image;

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $filename = time().'_'.$file->getClientOriginalName();

            $file->storeAs('products', $filename, 'public');
        }

        $product->update([
            'name' => $request->name ?? $product->name,
            'slug' => $request->slug ?? $product->slug,
            'description' => $request->description ?? $product->description,
            'price' => $request->price ?? $product->price,
            'stock' => $request->stock ?? $product->stock,
            'image' => $filename
        ]);

        return response()->json([
            "message" => "Product updated",
            "data" => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if(!$product){
            return response()->json([
                "message" => "Product not found"
            ],404);
        }

        $product->delete();

        return response()->json([
            "message" => "Product deleted"
        ]);
    }

    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->string('image');

            $table->timestamps();
        });
    }   
    
    public function deleteImage($id)
    {
        $image = ProductImage::find($id);

        if(!$image){
            return response()->json([
                "message"=>"Image not found"
            ],404);
        }

        $image->delete();

        return response()->json([
            "message"=>"Image deleted"
        ]);
    }

}