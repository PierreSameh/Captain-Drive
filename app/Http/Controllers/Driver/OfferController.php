<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;
use App\Models\RideRequest;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Ride;



class OfferController extends Controller
{
    use HandleTrait;

    public function showNearRequests(Request $request) {
        $driver = $request->user();
        $radius = 6371; // Earth's radius in kilometers
        $vehicle = Vehicle::where('driver_id', $driver->id)->first();
        $requests = RideRequest::select('ride_requests.*', DB::raw("
                ($radius * acos(cos(radians(?)) 
                * cos(radians(ride_requests.st_lat)) 
                * cos(radians(ride_requests.st_lng) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(ride_requests.st_lat)))) AS distance"))
            ->addBinding([$driver->lat, $driver->lng, $driver->lat], 'select')
            ->having('distance', '<', 10)
            ->orderBy('distance', 'asc')
            ->where('vehicle', $vehicle->type)
            ->where('status', "pending")
            ->with('stops') // Eager load the 'stops' relationship
            ->get();
        if (count( $requests ) > 0) {
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "requests" => $requests
            ],
            []
        );
        }
        return $this->handleResponse(
            true,
            "Waiting For Nearby Requests",
            [],
            [],
            []
        );
    }
    
    public function makeOffer(Request $request, $requestId){
        $validator = Validator::make($request->all(), [
            "price"=> ["required", "numeric"],
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
        $driver = $request->user();
        $exists = Offer::where("driver_id", $driver->id)
        ->where("request_id", $requestId)
        ->first();
        if (isset($exists)) {
            return $this->handleResponse(
                false,
                "You Can't Send More Than 1 Offer",
                [],
                [],
                []
            );
        }
        $rideRequest = RideRequest::find($requestId);
        if ($rideRequest){
            $offer = new Offer();
            $offer->driver_id = $driver->id;
            $offer->request_id = $rideRequest->id;
            $offer->price = $request->price;
            $offer->save();
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "offer" => $offer
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Ride Request is No Longer Available",
            [],
            [],
            []
        );
    }

    public function getOfferDriver(Request $request) {
        $driver = $request->user();
        $offer = Offer::where("driver_id", $driver->id)->where('status', "pending")->first();
        if (isset($offer)) {
            return $this->handleResponse(
                true,
                "Your Placed Offer",
                [],
                [
                    "offer"=> $offer
                ],
                []
                );
            }
            return $this->handleResponse(
                false,
                "No Placed Offers",
                [],
                [],
                []
            );
    }

    public function cancelOffer(Request $request, $offerId) {
        $driver = $request->user();
        $lastOffer = Offer::where("driver_id", $driver->id)
        ->where("id", $offerId)->first();
        if (isset($lastOffer)) {
            $lastOffer->delete();
            return $this->handleResponse(
                true,
                "Offer Canceled",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Offer Not Found",
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
        ->whereNotIn('status', ['completed', 'canceled', 'canceled_driver'])
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

    public function cancelRideByDriver(Request $request){
        $driverId = $request->user()->id;
        $ride = Ride::whereHas('offer', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })
        ->whereNotIn('status', ['completed', 'canceled'])
        ->with(['offer.request'])
        ->first();
        if($ride){
        $ride->status = "canceled_driver";
        $ride->save();
        return $this->handleResponse(
            true,
            "Ride Request Canceled",
            [],
            [
                "ride" => $ride
            ],
            []
        );
        }
        return $this->handleResponse(
            false,
            "Ride Not Found",
            [],
            [],
            []
        );
    }
}
