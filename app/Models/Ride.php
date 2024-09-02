<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        "offer_id",
        "status",
    ];

    public function offer(){
        return $this->belongsTo(Offer::class);
    }

    public function video(){
        return $this->hasOne(Video::class);
    }
}
