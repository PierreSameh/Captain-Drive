<?php

namespace App\Http\Controllers\User;

use App\HandleTrait;
use App\SendMailTrait;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

class AddressController extends Controller
{
    use HandleTrait;
    public function addAddress(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            "country"=> ["required", "string", "max:255"],
            "city"=> ["required", "string","max:255"],
            "address"=> ["required", "string","max:255"]
        ]);
        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()],
                [],
                []
            );
        }
        $user = $request->user();
        $address = Address::create([
            "user_id"=> $user->id,
            "country"=> $request->country,
            "city"=> $request->city,
            "address"=> $request->address,
        ]);
        if ($address) {
        return $this->handleResponse(
            true,
            "Address Added Successfully",
            [],
            [$user, $address],
            []
            );
        }
         } catch (\Exception $e) {
        return $this->handleResponse(
            false,
            "Couldn't Add Your Address",
            [$e->getMessage()],
            [],
            []
        );
        }
    }
}
