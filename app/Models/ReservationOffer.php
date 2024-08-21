<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        "driver_id",
        "reservation_request_id",
        "price",
        "status"
    ];

    public function driver(){
        return $this->belongsTo(Driver::class);
    }

    public function reservationRequest(){
        return $this->belongsTo(ReservationRequest::class);
    }

    public function reservation(){
        return $this->has(Reservation::class);
    }
}
