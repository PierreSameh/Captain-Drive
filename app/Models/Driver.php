<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Laravel\Sanctum\HasApiTokens; // If you're using Laravel Sanctum

class Driver extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        "name",
        "email",
        "phone",
        "add_phone",
        "password",
        "national_id",
        "picture",
        "status",
        "gender",
    ];

    public function driverdocs() {
        return $this->hasOne(DriverDoc::class,"id","driver_id");
    }
    public function cars() {
        return $this->hasOne(Vehicle::class,"id","driver_id");
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
