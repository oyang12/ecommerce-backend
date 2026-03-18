<?php

namespace App\Http\Api;

use App\Http\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    // tambah wishlist
    public function add(Request $request)
    {
        $request->validate([
            'product_id'=>'required|exists:products,id'
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id'=>$request->user()->id,
            'product_id'=>$request->product_id
        ]);

        return response()->json([
            'message'=>'Produk ditambahkan ke wishlist',
            'data'=>$wishlist
        ]);
    }

    // lihat wishlist
    public function index(Request $request)
    {
        $wishlist = Wishlist::with('product.images')
                    ->where('user_id',$request->user()->id)
                    ->get();

        return response()->json($wishlist);
    }

    // hapus wishlist
    public function remove(Request $request)
    {
        $request->validate([
            'product_id'=>'required|exists:products,id'
        ]);

        Wishlist::where('user_id',$request->user()->id)
            ->where('product_id',$request->product_id)
            ->delete();

        return response()->json([
            'message'=>'Produk dihapus dari wishlist'
        ]);
    }
}
