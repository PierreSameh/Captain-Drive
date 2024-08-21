<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStop extends Model
{
    use HasFactory;

    protected $fillable = [
        "reservation_request_id",
        "stop_location",
        "lng",
        "lat"
    ];

    public function reservationRequest(){
        return $this->belongsTo(ReservationRequest::class);
    }
}
