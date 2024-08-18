<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "vehicle",
        "st_location",
        "en_location",
        "st_lng",
        "st_lat",
        "en_lng",
        "en_lat",
        "status",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function stops(){
        return $this->hasMany(RideRequestStop::class);
    }

    public function offers(){
        return $this->hasMany(Offer::class);
    }
}
