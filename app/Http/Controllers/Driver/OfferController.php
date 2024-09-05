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
use App\Models\Wallet;
use App\Models\Profit;



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
            ->where('type', 'ride')
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
    
    public function makeOffer(Request $request){
        $validator = Validator::make($request->all(), [
            "price"=> ["required", "numeric"],
            "ride_request_id"=> 'required'
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
        ->where("request_id", $request->ride_request_id)
        ->whereNot("status", "canceled")
        ->first();
        $canceled = RideRequest::where('id', $request->ride_request_id)->where('status', 'canceled')->first();
        if($canceled){
            return $this->handleResponse(
                false,
                "Passenger Canceled Ride Request",
                [],
                [],
                []
            );
        }
        if (isset($exists)) {
            return $this->handleResponse(
                false,
                "You Can't Send More Than 1 Offer",
                [],
                [],
                []
            );
        }
        $rideRequest = RideRequest::find($request->ride_request_id);
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
        $offers = Offer::with('request', 'request.stops')
        ->whereHas('request', function($q) {
            $q->where('status', 'pending');
        })
        ->where("driver_id", $driver->id)->where('status', "pending")
        ->get();
        if (count($offers) > 0) {
            return $this->handleResponse(
                true,
                "Your Placed Offers",
                [],
                [
                    "offers"=> $offers
                ],
                []
                );
            }
            return $this->handleResponse(
                true,
                "No Placed Offers",
                [],
                [],
                []
            );
    }

    public function cancelOffer(Request $request) {
        $validator = Validator::make($request->all(), [
            "offer_id"=> 'required'
        ]);
        if($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }        $driver = $request->user();
        $lastOffer = Offer::where("driver_id", $driver->id)
        ->where("id", $request->offer_id)->first();
        if (isset($lastOffer)) {
            $lastOffer->status = 'canceled';
            $lastOffer->save();
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
        ->whereNotIn('status', ['completed', 'canceled_driver'])
        ->with(['offer.request', 'offer.request.stops'])
        ->latest()->first();
        if($ride){
            if($ride->status == 'canceled_user'){
                return $this->handleResponse(
                    false,
                    "Passenger Canceled The Ride",
                    [],
                    [],
                    []
                );
            }
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
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.request.stops'])
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

    public function setArrived(Request $request){
        $driverId = $request->user()->id;
        $ride = Ride::whereHas('offer', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.request.stops'])
        ->first();
        if($ride){
        $ride->status = "arrived";
        $ride->save();
        return $this->handleResponse(
            true,
            "Driver Arrived! Enjoy Your Trip",
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
    public function setCompleted(Request $request){
        $driverId = $request->user()->id;
        $ride = Ride::whereHas('offer', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request','offer.request.stops'])
        ->first();
        if($ride){
        $ride->status = "completed";
        $ride->save();
        $wallet = Wallet::where('driver_id', $driverId)->first();
        $profit = Profit::first();
        if($profit){
        $share = $ride->offer->price * ($profit->percentage / 100);
        $wallet->balance = $wallet->balance - $share;
        $wallet->save();
        }
        return $this->handleResponse(
            true,
            "You Have Reached Your Destination, Have A Nice Day!",
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

    public function activities(Request $request){
        $driverId = $request->user()->id;
        $activities = Ride::whereHas('offer', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })
        ->where('status', 'completed')
        ->with(['offer.request', 'offer.request.stops'])
        ->paginate(20);
        if(count($activities) > 0){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    $activities
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            "You Have No Activities Yet",
            [],
            [],
            []
        );
    }
}
