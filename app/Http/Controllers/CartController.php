<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;

class CartController extends Controller
{  //get cartId private method can't be passed on api routes instead used within this class
    private function getCartId($userId){
        $cart = DB::table('carts')->where('user_id', $userId)->first();

        if(!$cart){
           return DB::table('carts')->insertGetId([
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now()

            ]);

        }
        return $cart->id;
    }
    public function addToCart(Request $request){
        $cartId = $this->getCartId(auth()->id());

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1'
        ]);

        $item = DB::table('cart_items')->where('cart_id', $cartId)
                ->where('product_id', $request->product_id)->first();

        if($item){
             DB::table('cart_items')->where('id', $item->id)
            ->increment('quantity', $request->quantity);
        }
        else{
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now()
            ]);
           
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart successfully!'
        ], 201);

    }
    
    public function viewCart(){
        $userId = auth()->id();

       $viewMyCart =  DB::table('cart_items')->join('products', 'cart_items.product_id', '=', 'products.id')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')->where('carts.user_id', $userId)->select(
                'cart_items.id as ID', 'products.id as Product ID', 'products.name as Product Name', 
                'products.price as Price',
               'cart_items.quantity as Quantity'
            )->get();

        if($viewMyCart->isEmpty()){
            return response()->json([
                 'status' => 'success',
                 'message' => 'sorry you don\'t have any products in a cart'
                ], 200);
            }
        else{
                 return response($viewMyCart);
            }

        
    }
    public function removeCart($cartItemId){
        $cartItem = DB::table('cart_items')->where('id', $cartItemId)->first();

        if(!$cartItem){
            return response()->json(['message' => 'no item found in cart']);
        }

        DB::table('cart_items')->where('id', $cartItemId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'cart item removed successfully!'
        ], 200);
    }
}
