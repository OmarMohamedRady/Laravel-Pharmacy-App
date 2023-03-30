<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    public function area(){
        return $this->belongsTo(Area::class);
    }
   
   
    public function orders(){

        return $this->hasMany(Order::class);
    }

    public function client()
    {
        return $this->belongsTo('Client');
    }

    
}
