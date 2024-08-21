<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\Wallet;
use App\Models\ReservationRequest;
use App\Models\ReservationOffer;

class ReservationDController extends Controller
{
    use HandleTrait;

    public function showNearReservations(Request $request) {
        $driver = $request->user();
        $radius = 6371; // Earth's radius in kilometers
        $vehicle = Vehicle::where('driver_id', $driver->id)->first();
        $requests = ReservationRequest::select('reservation_requests.*', DB::raw("
                ($radius * acos(cos(radians(?)) 
                * cos(radians(reservation_requests.st_lat)) 
                * cos(radians(reservation_requests.st_lng) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(reservation_requests.st_lat)))) AS distance"))
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
        $exists = ReservationOffer::where("driver_id", $driver->id)
        ->where("reservation_request_id", $request->reservation_request_id)
        ->whereNot("status", "canceled")
        ->first();
        $canceled = ReservationRequest::where('id', $request->reservation_request_id)->where('status', 'canceled')->first();
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
        $rideRequest = ReservationRequest::find($request->reservation_request_id);
        if ($rideRequest){
            $offer = new ReservationOffer();
            $offer->driver_id = $driver->id;
            $offer->reservation_request_id = $rideRequest->id;
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
        $offers = ReservationOffer::with('reservationRequest')
        ->whereHas('reservationRequest', function($q) {
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
        $lastOffer = ReservationOffer::where("driver_id", $driver->id)
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
}
