<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "captain_name",
        "vehicle_plate",
        "ride_date",
        "report",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
