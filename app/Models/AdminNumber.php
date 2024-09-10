<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        "type",
        "name",
        "reference",
        "notes"
    ];
}
