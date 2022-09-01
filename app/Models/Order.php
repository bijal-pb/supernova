<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderPhoto;

class Order extends Model
{
    use HasFactory ,SoftDeletes;

    public function order_by()
    {
        return $this->hasOne(User::class,'id','order_by');
    }

    public function provider_detail()
    {
        return $this->hasOne(User::class,'id','staff_id');
    }

    public function service_booked()
    {
        return $this->hasMany(OrderItem::class,'order_id')->with(['service_item']);
    }

    public function photos()
    {
        return $this->hasMany(OrderPhoto::class,'order_id');
    }

    public function assign_to()
    {
        return $this->hasOne(User::class,'id','staff_id');
    }
}
