<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;

class Service extends Model
{
    use HasFactory,SoftDeletes;

    public function category()
    {
        return $this->hasOne(Category::class,'id','category_id');
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('/service/' . $value);
        } else {
            return null;
        }
    }
    
}
