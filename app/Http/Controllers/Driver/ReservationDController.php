<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Wallet;
use App\Models\RideRequest;
use App\Models\RideRequestStop;
use App\Models\Offer;
use App\Models\Ride;


class ReservationDController extends Controller
{
    use HandleTrait;

    public function showNearReservations(Request $request) {
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
            ->having('distance', '<', 2)
            ->orderBy('distance', 'asc')
            ->where('vehicle', $vehicle->type)
            ->where('status', "pending")
            ->where('type', 'reservation')
            ->with('stops') // Eager load the 'stops' relationship
            ->get();
        if (count( $requests ) > 0) {
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "reservation_requests" => $requests
            ],
            []
        );
        }
        return $this->handleResponse(
            true,
            "Waiting For Nearby Reservation Requests",
            [],
            [],
            []
        );
    }

    public function makeReservationOffer(Request $request){
        $validator = Validator::make($request->all(), [
            "price"=> ["required", "numeric"],
            "reservation_request_id"=> 'required'
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
        ->where("request_id", $request->reservation_request_id)
        ->whereNot("status", "canceled")
        ->first();
        $canceled = RideRequest::where('id', $request->reservation_request_id)->where('status', 'canceled')->first();
        if($canceled){
            return $this->handleResponse(
                false,
                "Passenger Canceled Reservation Request",
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
        $rideRequest = RideRequest::find($request->reservation_request_id);
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
                    "reservation_offer" => $offer
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Reservation Request is No Longer Available",
            [],
            [],
            []
        );
    }

    public function getReservationOffer(Request $request) {
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
                "Your Placed Reservation Offers",
                [],
                [
                    "reservation_offers"=> $offers
                ],
                []
                );
            }
            return $this->handleResponse(
                true,
                "No Placed Reservation Offers",
                [],
                [],
                []
            );
    }
    
    public function cancelReservationOffer(Request $request) {
        $validator = Validator::make($request->all(), [
            "reservation_offer_id"=> 'required'
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
        ->where("id", $request->reservation_offer_id)->first();
        if (isset($lastOffer)) {
            $lastOffer->status = 'canceled';
            $lastOffer->save();
            return $this->handleResponse(
                true,
                "Reservation Offer Canceled",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Reservation Offer Not Found",
            [],
            [],
            []
        );
    }

    public function getReservation(Request $request){
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
                    "Passenger Canceled The Reservation",
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
                    "reservation" => $ride
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            "No Active Reservations",
            [],
            [],
            []
        );
        
        
    }

    public function cancelReservation(Request $request){
        $driverId = $request->user()->id;
        $ride = Ride::where('id', $request->reservation_id)
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.request.stops'])
        ->first();
        if($ride){
        $ride->status = "canceled_driver";
        $ride->save();
        return $this->handleResponse(
            true,
            "Reservation Canceled",
            [],
            [
                "reservation" => $ride
            ],
            []
        );
        }
        return $this->handleResponse(
            false,
            "Reservation Not Found",
            [],
            [],
            ['Enter Reservation ID Correctly']
        );
    }

    public function setArriving(Request $request){
        $validator = Validator::make($request->all(), [
            'ride_id'=> 'required',
        ]);
        if( $validator->fails() ){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }
        $ride = Ride::where('id', $request->ride_id)
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.request.stops'])
        ->first();
        if($ride){
        $ride->status = "arriving";
        $ride->save();
        return $this->handleResponse(
            true,
            "Driver is arriving",
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
