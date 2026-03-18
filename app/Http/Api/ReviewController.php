<?php

namespace App\Http\Api;

use App\Http\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{

    // tambah review
    public function store(Request $request)
    {
        $request->validate([
            'product_id'=>'required|exists:products,id',
            'rating'=>'required|integer|min:1|max:5',
            'review'=>'nullable|string'
        ]);

        $review = Review::create([
            'user_id'=>$request->user()->id,
            'product_id'=>$request->product_id,
            'rating'=>$request->rating,
            'review'=>$request->review
        ]);

        return response()->json([
            'message'=>'Review berhasil dibuat',
            'data'=>$review
        ]);
    }

    // lihat review produk
    public function productReviews($product_id)
    {
        $reviews = Review::with('user')
            ->where('product_id',$product_id)
            ->latest()
            ->get();

        return response()->json($reviews);
    }
}
