<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPhoto extends Model
{
    use HasFactory;

    public function getPhotoAttribute($value)
    {
        if ($value) {
            return asset('/orderImages/' . $value);
        } else {
            return null;
        }
    }
}
