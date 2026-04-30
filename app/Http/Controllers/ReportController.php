<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function productList(){
        $products = DB::table('orders')->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.payment_status', 'paid')
                    ->select(
                        'products.name as Product Name',
                       DB::raw('SUM(order_items.quantity) as Quantity') ,
                       DB::raw('ROUND(SUM(order_items.price)) as unit_price'),
                       DB::raw('ROUND(SUM(order_items.quantity * order_items.price)) as total_amount')
                        
                    )->groupBy('products.name')->orderBy('Quantity', 'desc')->get();

        return response()->json($products);

    }


    public function customerReceipt(Request $request){
        $userId = auth()->id();
        $request->validate([
            'order_id' => 'required|numeric|exists:orders,id'
        ],
        ['order_id.exists' => 'order no. is not found']);

        $receipt = DB::table('orders')->join('order_items', 'orders.id', '=', 'order_items.order_id')->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('users', 'orders.customer_id', '=', 'users.id')->where('orders.customer_id', $userId)
                    ->where('orders.payment_status', 'paid')
                    ->where('orders.id', $request->order_id)
                    ->select(
                        'orders.id as Order No',
                        'orders.created_at as Order Date',
                        'products.name as Product Name',
                        'order_items.price as unit_price',
                        'order_items.quantity as quantity',
                        'orders.total_amount as total_amount'
                    )
                    ->first();

        return response()->json($receipt);
                
    
    }
}
