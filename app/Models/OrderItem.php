<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory ,SoftDeletes;

    public function service_item()
    {
        return $this->hasOne(ServiceItem::class,'id','service_item_id')->with('service');
    }
}
