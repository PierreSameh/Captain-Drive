<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Ride;
use App\HandleTrait;
use App\Models\RideRequest;
use App\Models\RideRequestStop;
use Illuminate\Support\Facades\DB;

class RideController extends Controller
{
    use HandleTrait;

    public function sendRideRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "vehicle"=> ["required", "numeric", "in:1,2,3,4,5"],
            "st_location"=> ["required","string","max:255"],
            "en_location"=> ["required","string","max:255"],
            "stop_locations.*"=> ["array:stop_location,lng,lat"],
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
            $stopLocations = [];
        if ($request->stop_locations) {
            foreach ($request->stop_locations as $stop_location) {
    
                $stop = RideRequestStop::create([
                    "ride_request_id"=> $rideRequest->id,
                    'stop_location' => $stop_location['stop_location'],
                    'lng' => $stop_location['lng'],
                    'lat'=> $stop_location['lat']
                ]);

                $stopLocations[] = $stop;
            } 

            return $this->handleResponse(
                true,
                "Ride Request Sent Successfully",
                [],
                [
                    "Request" => $rideRequest,
                    "stops"=> $stopLocations
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

    public function getForUserRideRequest(Request $request) {
        $user = $request->user();
        $ride = RideRequest::where("user_id", $user->id)->with('stops')->latest()->limit(1)->get();
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

    public function getRideUser(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled'])
        ->with(['offer.request'])
        ->first();
        if($ride){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "ride" => $ride
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            "No Active Rides",
            [],
            [],
            []
        );
        
        
    }
    public function getRideDriver(Request $request){
        $driverId = $request->user()->id;
        $ride = Ride::whereHas('offer', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })
        ->whereNotIn('status', ['completed', 'canceled'])
        ->with(['offer.request'])
        ->first();
        if($ride){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "ride" => $ride
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            "No Active Rides",
            [],
            [],
            []
        );
        
        
    }
}
