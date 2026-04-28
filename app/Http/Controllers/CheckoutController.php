<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(Request $request){
        $userId = auth()->id();

        $request->validate([
            'phone' => 'required|string',
            'address' => 'required|string'
        ]);


        $items = DB::table('cart_items')->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                ->join('products', 'cart_items.product_id', '=', 'products.id')->where('carts.user_id', $userId)->
                select(
                    'cart_items.product_id', 
                    'cart_items.quantity',
                    'products.price',
                    'products.stock_quantity'
                    
                )->get();


        if($items->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'cart is empty'
            ], 400);
        }

        DB::beginTransaction();

        try{
           
            $total = 0;

             foreach($items as $item){
               if($item->stock_quantity < $item->quantity){

                throw new Exception("insuffient stock for productId {$item->product_id}");
               }
               
            }

            foreach($items as $item){
                $total += $item->price * $item->quantity;
            }

            $orderId  = DB::table('orders')->insertGetId([
                'customer_id' => $userId,
                'total_amount' => $total,
                'phone' => $request->phone,
                'address' => $request->address,
                'created_at' => now(),
                'updated_at' => now()

            ]);


            foreach($items as $item){
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ]);

                DB::table('products')->where('id', $item->product_id)
                ->decrement('stock_quantity', $item->quantity);
            }

            DB::table('cart_items')->whereIn('cart_id', function($query){
                $query->select('id')->from('carts')->where('user_id', auth()->id());
            })->delete();


            DB::commit();


            return response()->json(
                ['status' => 'success',
                'message' => 'order placed successfully!'
            ], 200
            );




        }

        catch (Exception $e){

            Log::error('product order error'. $e->getMessage());

            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => 'internal server error',
                'error' => $e->getMessage()
            ], 500);

        }
    }
}
