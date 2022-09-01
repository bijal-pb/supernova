<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Follower extends Model
{
    use HasFactory;

    public function follow_user()
    {
        $this->hasOne(User::class,'id','follow_to');        
    }

}
