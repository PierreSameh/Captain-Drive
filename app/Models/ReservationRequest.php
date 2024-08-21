<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "st_location",
        "en_location",
        "vehicle",
        "st_lng",
        "st_lat",
        "en_lng",
        "en_lat",
        "status",
        "time"
    
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function stops(){
        return $this->hasMany(ReservationStop::class);
    }

    public function reservationOffers(){
        return $this->hasMany(ReservationOffer::class);
    }
}
