<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        "reservation_offer_id",
        "status",
        "rate",
        "review"
    ];

    public function reservationOffer(){
        return $this->belongsTo(ReservationOffer::class);
    }
}
