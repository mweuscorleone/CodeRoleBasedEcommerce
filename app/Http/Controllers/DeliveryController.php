<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function assignDriver(Request $request){
        $request->validate([
            'order_id' => 'required|numeric|exists:orders,id',
            'driver_id' => 'required|numeric|exists:users,id'],
            [
                'order_id.exists' => 'OrderId not found',
                'driver_id.exists' => 'Driver is not found'
            ]);
           $driver = DB::table('users')->where('role', 'driver')
           ->where('id', $request->driver_id)->first();
            
            if(!$driver){
                return response()->json([
                    'message' => 'selected user is not driver'
                ]);
            }
            $order = DB::table('orders')->where('id', $request->order_id)->first();
            if(!$order){
                return response()->json([
                    'message' => 'there is no order matching to your choice'
                ]);
            }

       try{
        DB::table('orders')->where('id', $request->order_id)->update([
            'driver_id' => $request->driver_id,
            'order_status' => 'assigned'
        ]);

         return response()->json([
            'status' => 'success',
            'message' => 'driver assigned successfully!'
        ], 200);
       } 
       catch (Exception $e) {
         Log::error('driver assignment error' . $e.getMessage());


         return response()->json([
            'status' => 'error',
            'message' => 'something went wrong',
            'error' => $e.getMessage()
         ], 500);
       }

       
    }

    public function deliverOrder(Request $request){
        $userId = auth()->id();
        $request->validate([
            'order_id' => 'required|numeric|exists:orders,id'],
            [
                'order_id.exists' => 'Order Id is not found'
            ]);

            DB::table('orders')->where('id', $request->order_id)
            ->where('driver_id', $userId)
            ->update([
                'order_status' => 'delivered',
                'payment_status' => 'paid',
                'created_at' => now() 
            ]);

            return response()->json([
                'message' => 'Order Delevered and Paid successfully!'
            ], 200);


    }

    public function driverOrder(Request $request){
        $userId = auth()->id();
        $userName = auth()->user()->name;
        $request->validate([
            'order_status' => 'required|in:pending,assigned,delivered'
        ],
        [
            'order_status.in' => "order status must be pending, assigned or delivered"
        ]
);

        $driverOrder = DB::table('orders')->join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('users', 'orders.customer_id', '=', 'users.id')
                    ->where('orders.driver_id', $userId)
                    ->where('orders.order_status', $request->order_status)
                    ->select(
                        'orders.id as Order No',
                        'users.name as Customer Name',
                        'orders.address as Address',
                        'orders.payment_status as Payment status' ,
                        'orders.order_status as Order status',
                        'orders.payment_method as Payment Method',
                        DB::raw("(CASE WHEN 
                                orders.order_status = 'delivered'
                                THEN orders.created_at
                                ELSE NULL
                                END) as Delivered_Time")
                       
                        

                    )->orderBy('Delivered_Time', 'desc')->get();

        return response()->json($driverOrder);

    }

}
