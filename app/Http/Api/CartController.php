<?php

namespace App\Http\Controllers\Api;

use App\Http\Api\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // ===============================
    // Tambah item ke cart
    // ===============================
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = $cart->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->quantity += $request->quantity ?? 1;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity ?? 1,
                'price' => $product->price
            ]);
        }
        
        $product = Product::findOrFail($request->product_id);

        // cek stock
        if ($product->stock <= 0) {
            return response()->json([
                'message' => 'Stock produk habis'
            ], 400);
        }

        $qty = $request->quantity ?? 1;

        if ($qty > $product->stock) {
            return response()->json([
                'message' => 'Jumlah melebihi stock'
            ], 400);
        }

        // Response sangat minimal
        $cartItems = $cart->items()->get(['product_id', 'quantity', 'price']);

        return response()->json([
            'message' => 'Item ditambahkan ke cart',
            'cart_items' => $cartItems
        ]);
    }

    // ===============================
    // Lihat cart
    // ===============================
    public function viewCart(Request $request)
    {
        $cart = Cart::with('items.product')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart kosong'], 404);
        }

        $total = $cart->items->sum(function($item){
            return $item->price * $item->quantity;
        });

        return response()->json([
            'cart' => $cart,
            'total_price' => $total
        ]);
    }

    // ===============================
    // Hapus item dari cart
    // ===============================
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $cart = Cart::with('items')->where('user_id', $request->user()->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart kosong'], 404);
        }

        $item = $cart->items()->where('product_id', $request->product_id)->first();
        if ($item) {
            $item->delete();
        }

        return response()->json([
            'message' => 'Item dihapus dari cart',
            'cart' => $cart->load('items.product')
        ]);
    }

    // ===============================
    // Checkout → buat order + order_items
    // ===============================
    public function checkout(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('items')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 400);
        }

        DB::transaction(function () use ($cart, $user) {

            $total = 0;

            foreach ($cart->items as $item) {

                // LOCK product row
                $product = Product::where('id', $item->product_id)
                            ->lockForUpdate()
                            ->first();

                if ($product->stock < $item->quantity) {
                    throw new \Exception("Stock produk {$product->name} tidak cukup");
                }

                $total += $item->price * $item->quantity;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => 'pending'
            ]);

            foreach ($cart->items as $item) {

                $product = Product::where('id', $item->product_id)
                            ->lockForUpdate()
                            ->first();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ]);

                // kurangi stock
                $product->decrement('stock', $item->quantity);
            }

            // kosongkan cart
            $cart->items()->delete();

        });

        return response()->json([
            'message' => 'Checkout berhasil'
        ]);
    }

    // ===============================
    // Lihat semua order user
    // ===============================
    public function listOrders(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at','desc')
            ->paginate(10);

        return response()->json($orders);
    }

    // ===============================
    // Lihat detail order tertentu
    // ===============================
    public function viewOrder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items.product'));
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart tidak ditemukan'
            ], 404);
        }

        $item = $cart->items()->where('product_id', $request->product_id)->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item tidak ada di cart'
            ], 404);
        }

        $product = Product::find($request->product_id);

        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Jumlah melebihi stock'
            ], 400);
        }

        $item->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'message' => 'Quantity berhasil diupdate',
            'item' => $item
        ]);
    }

    public function clearCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 404);
        }

        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart berhasil dikosongkan'
        ]);
    }


}
