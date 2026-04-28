<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function myOrder(){
        $userID = auth()->id();

        $order = DB::table('orders')->join('order_items', 'orders.id','=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.customer_id', $userID)
                ->select('orders.id as OrderID',
                         'products.name as Product Name',
                        'order_items.quantity as Quantity', 
                        'orders.payment_status as Payment status',
                        'orders.order_status',
                        'orders.created_at as Order Date'
                )->orderBy('Order Date', 'desc')->get();

        return response()->json($order);
    }
}
