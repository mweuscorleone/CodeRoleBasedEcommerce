<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{   use HasFactory;
     
    protected $fillable = ['customer_id', 'total_amount','payment_method','payment_status',
                            'order_status', 'driver_id'];

    public function customers(){
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function drivers(){
        return $this->belongsTo(User::class, 'driver_id');
    }
    public function order_items(){
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
