<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function createProduct(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|numeric',
            'status' => 'sometimes|in:availabla,unavailable'
        ]);

      $productId =  DB::table('products')->insertGetId([
                    'name' => $request->name,
                    'description' => $request->description,
                    'price' => $request->price,
                    'stock_quantity' => $request->stock_quantity,
                    'status' => $request->status ?? 'available',
                    'created_at' => now(),
                    'updated_at' => now()
      ]);

      $product = DB::table('products')->where('id', $productId)->first();

      return response()->json([
        'status' => 'success',
        'message' => 'product created successfully!',
        'product' => $product
      ], 201);
    }

    public function updateProduct(Request $request, $prodId){
        $product = DB::table('products')->where('id', $prodId)->first();

        if(!$product){
            return response()->json(['message' => 'product not found'], 404);
        }

        $fields = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock_quantity' => 'sometimes|numeric',
            'status' => 'sometimes|in:available,unavailable'
        ]);

        if(empty($fields)){
            return response()->json(['message' => 'no value changed'], 400);
        }

         DB::table('products')->where('id', $prodId)->update($fields);

         $updatedProduct = DB::table('products')->where('id', $prodId)->first();
        

        return response()->json([
            'status' => 'success',
            'message' => 'product updated successfully!',
            'updated field(s)' => array_keys($fields),
            'product'  => $updatedProduct
        ], 200);
    }

    public function removeProduct($prodId){
        $product = DB::table('products')->where('id', $prodId)->first();

        if(!$product){
            return response()->json([
                'message' => 'no product found matching the id provided'
            ]);
        }

        $deleted = DB::table('products')->where('id', $prodId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => $deleted . ' product(s) deleted successfully!'
        ], 200);
    }
    public function productList(){
        
        $products = DB::table('products')->
        select(
            'id as Product ID',
            'name as Product Name',
            'description as Product Details',
            DB::raw('ROUND(price) as Price'),
            'stock_quantity as Balance',
            'status'

        )->paginate(25);

        if(!$products){
            return response()->json(['message' => 'no product availabe'], 404);
        }

        return response()->json($products);
    }
}
