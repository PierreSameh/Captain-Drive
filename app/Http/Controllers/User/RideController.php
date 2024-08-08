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
        $user = $request->user();
        $st_lng = $request->st_lng;
        $st_lat = $request->st_lat;
        $en_lng = $request->en_lng;
        $en_lat = $request->en_lat;
        if ($user && $st_lng && $st_lat && $en_lng && $en_lat){
            $rideRequest = RideRequest::create([
                "user_id"=> $user->id,
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
}
