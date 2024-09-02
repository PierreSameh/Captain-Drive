<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        "ride_id",
        "title",
        "notes",
        "path",
    ];


    public function ride(){
        return $this->belongsTo(Ride::class);
    }
}
