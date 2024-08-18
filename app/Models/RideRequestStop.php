<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideRequestStop extends Model
{
    use HasFactory;

    protected $fillable = [
        "ride_request_id",
        "stop_location",
        "lng",
        "lat",
    ];

    public function riderequest(){
        return $this->belongsTo(RideRequest::class);
    }
}
