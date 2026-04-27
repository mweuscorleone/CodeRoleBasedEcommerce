<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{ use HasFactory;

    protected $fillable = ['name', 'description','price', 'status','stock_quantity'];


    public function cart_items(){
        return $this->hasMany(CartItem::class);
    }
    public function order_items(){
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}
