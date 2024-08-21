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
                [$validator->errors()->first()],
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

    public function updateAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            "address_id" => ["required"],
            "country"=> ["required", "string", "max:255"],
            "city"=> ["required", "string","max:255"],
            "address"=> ["required", "string","max:255"]
        ]);
        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }
        $address = Address::where("id", $request->address_id)->first();
        $user = User::where("id", $address->user_id)->first();
        if ($address) {
            $address->country = $request->country;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->save();
            return $this->handleResponse(
                true,
                "Address Updated Successfully!",
                [],
                [$user, $address],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Couldn't Update Password",
            [],
            [],
            []
            );

    }

    public function getAllAddresses() {
        $addresses = Address::all();
        if (count($addresses) > 0 ) {
        return $this->handleResponse(
            true,
            "Addresses",
            [],
            [$addresses],
            []
            );
        }
        return $this->handleResponse(
            false,
            "Empty Addresses",
            [],
            [],
            []
        );
    
    }

    public function getUserAddresses(Request $request) {
        $user = $request->user();
        $addresses = Address::where("id", $user->id)->get();
        if (count($addresses) > 0 ) {
            return $this->handleResponse(
                true,
                "Addresses",
                [],
                [$user, $addresses],
                []
                );
            }
            return $this->handleResponse(
                false,
                "Empty Addresses",
                [],
                [],
                []
            );
    }

    public function getAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            "address_id"=> ['required']
        ]);
        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }
        $user = $request->user();
        $address = Address::where("id", $request->address_id)->first();
        if (isset($address)) {
            return $this->handleResponse(
                true,
                "Your Address",
                [],
                [$user, $address],
                []
                );
            }
            return $this->handleResponse(
                false,
                "Empty Addresses",
                [],
                [],
                []
            );
    }

    public function deleteAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            "address_id" => ['required'],
        ]);
        if($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }
        $address = Address::where('id', $request->address_id);
        if (isset($address)) {
        $address->delete();

        return $this->handleResponse(
            true,
            "Address Deleted Successfully",
            [],
            [],
            []
        );
        } else {
            return $this->handleResponse(
                false,
                "Couldn't Delete Your Pet",
                [],
                [],
                []
            );
        }
    }
}
