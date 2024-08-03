<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        "driver_id",
        "type",
        "model",
        "color",
        "plates_number",
    ];


    public function driver() {
        return $this->belongsTo(Driver::class);
    }
}
