<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{   use HasFactory;
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function products(){
        return $this->belongsToMany(Product::class);
    }
    public function carts(){
        return $this->belongsTo(Cart::class);
    }
}
