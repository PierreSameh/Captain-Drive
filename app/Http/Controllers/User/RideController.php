<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\HandleTrait;
use App\Models\RideRequest;

class RideController extends Controller
{
    use HandleTrait;

    public function sendRideRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "vehicle"=> ["required", "numeric", "in:1,2,3,4,5"],
            "st_location"=> ["required","string","max:255"],
            "en_location"=> ["required","string","max:255"],
        ]);
        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "Vehicle Types" => [
                        '1 -> Car',
                        '2 -> conditioned car',
                        '3 -> Motorcycle',
                        '4 -> Taxi',
                        '5 -> Bus'
                        ]
                ]
            );
        }

        $user = $request->user();
        $st_lng = $request->st_lng;
        $st_lat = $request->st_lat;
        $en_lng = $request->en_lng;
        $en_lat = $request->en_lat;
        if ($user && $st_lng && $st_lat && $en_lng && $en_lat){
            $rideRequest = RideRequest::create([
                "user_id"=> $user->id,
                "vehicle"=> $request->vehicle,
                "st_location"=> $request->st_location,
                "en_location"=> $request->en_location,
                "st_lng"=> $st_lng,
                "st_lat"=> $st_lat,
                "en_lng"=> $en_lng,
                "en_lat"=> $en_lat
            ]);

            return $this->handleResponse(
                true,
                "Ride Request Sent Successfully",
                [],
                [
                    "Request" => $rideRequest
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Can't Get Location",
            [],
            [],
            []
        );
    }

    public function getForUserRideRequest(Request $request) {
        $user = $request->user();
        $ride = RideRequest::where("user_id", $user->id)->first();
        if ($ride){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "reqeust"=> $ride
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "No Ride Reqeusts Found",
            [],
            [],
            []
            );
    }

    public function cancelRideRequest(Request $request, $rideID) {
        $ride = RideRequest::findOrFail($rideID);
        if (isset($ride)) {
            $ride->delete();
            return $this->handleResponse(
                true,
                "Ride Cancelled",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Can't Find The Ride",
            [],
            [],
            []
        );
    }
}
