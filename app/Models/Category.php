<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory ,SoftDeletes;
    
    protected $appends = ['total_services'];

    public function getTotalServicesAttribute()
    {
        return $this->hasMany(Service::class,'category_id')->count();
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('/category/' . $value);
        } else {
            return null;
        }
    }
}
