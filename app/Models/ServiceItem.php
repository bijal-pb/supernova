<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    use HasFactory,SoftDeletes;

    public function service()
    {
        return $this->hasOne(Service::class,'id','service_id')->with('category');
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('/serviceItem/' . $value);
        } else {
            return asset('/serviceItem/noImage.png');
        }
    }
}
