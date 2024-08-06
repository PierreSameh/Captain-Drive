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
        "super_key",
        "unique_id",
        "email_last_verfication_code",
        "email_last_verfication_code_expird_at",
        "remember_token",
    ];

    public function driverdocs() {
        return $this->hasOne(DriverDoc::class,"id","driver_id");
    }
    public function vehicle() {
        return $this->hasOne(Vehicle::class,"id","driver_id");
    }

    public function wallet() {
        return $this->hasOne(Wallet::class);
    }

    public function transaction() {
        return $this->hasMany(Transaction::class);
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
