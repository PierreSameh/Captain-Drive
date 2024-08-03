<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDoc extends Model
{
    use HasFactory;

    protected $fillable = [
        "driver_id",
        "national_front",
        "national_back",
        "driverl_front",
        "driverl_back",
        "vehicle_front",
        "vehicle_back",
        "criminal_record",
    ];

    public function driver() {
        return $this->belongsTo(Driver::class);
    }
}
