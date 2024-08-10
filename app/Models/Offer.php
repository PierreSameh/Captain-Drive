<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        "driver_id",
        "request_id",
        "price",
        "status",
    ];

    public function driver(){
        return $this->belongsTo(Driver::class);
    }

    public function request(){
        return $this->belongsTo(RideRequest::class);
    }
}
