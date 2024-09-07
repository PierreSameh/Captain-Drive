<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "driver_id",
        "amount",
        "status",
        "type",
    ];


    public function driver() {
        return $this->belongsTo(Driver::class);
    }
}
