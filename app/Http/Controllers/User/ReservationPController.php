<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use Illuminate\Support\Facades\DB;
use App\Models\RideRequest;
use App\Models\RideRequestStop;
use App\Models\Offer;
use App\Models\Ride;



class ReservationPController extends Controller
{
    use HandleTrait;

    public function sendReservationRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "vehicle"=> ["required", "numeric", "in:1,2,3,4,5"],
            "st_location"=> ["required","string","max:255"],
            "en_location"=> ["required","string","max:255"],
            "st_lng"=> ["required","string"],
            "st_lat"=> ["required","string"],
            "en_lng"=> ["required","string"],
            "en_lat"=> ["required","string"],
            "stop_locations.*"=> ["nullable","array:stop_location,lng,lat"],
            "time"=> "required|date_format:Y-m-d H:i:s",
            "price"=>["required", "numeric"],

        ]);
        
        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "time format example"=> "2024-05-02 17:09:00",
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
        $setRide = Ride::whereHas('offer.request', function($q) use($user){
            $q->where('user_id', $user->id)->where('type', 'reservation');
        })
        ->latest()->first();
        $setRequest = RideRequest::where('user_id', $user->id)->where('type','reservation')
        ->latest()->first();
        $isset1 = $setRide->status == 'completed' ? true : false;
        $isset2 = $setRequest->status == 'pending' ? false : true;
        if (!$isset1 || $isset2) {
            return $this->handleResponse(
                false,
                "You Can't Reserve Many Rides",
                [],
                [],
                []
                );
        }
        $st_lng = $request->st_lng;
        $st_lat = $request->st_lat;
        $en_lng = $request->en_lng;
        $en_lat = $request->en_lat;
        if ($user && $st_lng && $st_lat && $en_lng && $en_lat){
            $reservationRequest = RideRequest::create([
                "user_id"=> $user->id,
                "vehicle"=> $request->vehicle,
                "st_location"=> $request->st_location,
                "en_location"=> $request->en_location,
                "st_lng"=> $st_lng,
                "st_lat"=> $st_lat,
                "en_lng"=> $en_lng,
                "en_lat"=> $en_lat,
                "type"=> "reservation",
                "time"=> $request->time,
                "price"=> $request->price,
            ]);
            $stopLocations = [];
        if ($request->has("stop_locations")) {
            foreach ($request->stop_locations as $stop_location) {
    
                $stop = RideRequestStop::create([
                    "ride_request_id"=> $reservationRequest->id,
                    'stop_location' => $stop_location['stop_location'],
                    'lng' => $stop_location['lng'],
                    'lat'=> $stop_location['lat']
                ]);

                $stopLocations[] = $stop;
            } 
        }
            $withStops = RideRequest::where('id', $reservationRequest->id)->with('stops')->first();
            return $this->handleResponse(
                true,
                "Reservation Request Sent Successfully",
                [],
                [
                    "reservation_request" => $withStops,
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Can't Get Location",
            [],
            [],
            ["Enter lng & lat data correctly"]
        );
    }

    public function getForUserReservationRequest(Request $request) {
        $user = $request->user();
        $ride = RideRequest::where("user_id", $user->id)
        ->where('type', 'reservation')
        ->whereNotIn('status', ['canceled', 'closed'] )
        ->with('stops')->latest()->first();
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
            "No Reservation Reqeusts Found",
            [],
            [],
            []
            );
    }

    public function cancelReservationRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "reservation_request_id"=> 'required'
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
        $ride = RideRequest::findOrFail($request->reservation_request_id);
        if (isset($ride)) {
            $ride->status = "canceled";
            $ride->save();
            return $this->handleResponse(
                true,
                "Reservation Cancelled",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Can't Find The Reservation",
            [],
            [],
            []
        );
    }

    public function getAllReservationOffers(Request $request) {
        $user = $request->user();
        $rideRequest = RideRequest::where("user_id", $user->id)
        ->where("status", "pending")
        ->where('type', 'reservation')
        ->latest()->first();
        if (isset($rideRequest)) {
            $offers = Offer::where('request_id', $rideRequest->id)
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->with('driver.vehicle')
            ->get();
            if(count($offers) > 0) {
            return $this->handleResponse(
                true,
                "Reservation Offers",
                [],
                [
                    "offers" => $offers
                ],
                []
                );
            }
            return $this->handleResponse(
                true,
                "No Reservation Offers",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Reservation Request Not Available",
            [],
            [],
            []
            );
    }

    public function acceptReservationOffer(Request $request){
        try{
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
        }
        $offer = Offer::where('id', $request->reservation_offer_id)->first();
        $rideRequest = RideRequest::where('id', $offer->request_id)->first();
        if (isset($offer)) {
            $rideRequest->status = "closed";
            $rideRequest->save();

            $offer->status = "accepted";
            $offer->save();

            Ride::create([
                "offer_id" => $offer->id,
                "status"=> "waiting"
            ]);

            return $this->handleResponse(
                true,
                "Reservation Offer Accepted",
                [],
                [
                    "reservation_offer" => $offer,
                    "reservation_request" => $rideRequest
                ],
                []
                );
            }
        }catch(\Exception $e){
            return $this->handleResponse(
                false,
                "Reservation Offer Not Found",
                [$e->getMessage()],
                [],
                []
            );
        }
    }

    public function rejectReservationOffer(Request $request){
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
        }
        $user = $request->user();
        $offer = Offer::where('id', $request->reservation_offer_id)
        ->where('status', 'pending')
        ->first();
        if (isset($offer)) {
            $offer->status = "rejected";
            $offer->save();

            return $this->handleResponse(
                true,
                "Reservation Offer Rejected",
                [],
                [
                    "offer" => $offer,
                ],
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
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId)->where('type', 'reservation');
        })
        ->whereNotIn('status', ['completed', 'canceled_user'])
        ->with(['offer.request', 'offer.request.stops', 'offer.driver'])
        ->latest()->first();
        if($ride){
            if($ride->status == 'canceled_driver'){
                return $this->handleResponse(
                    false,
                    "Driver Canceled The Reservation",
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
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId)->where('type', 'reservation');
        })
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.driver'])
        ->latest()->first();
        if($ride){
        $ride->status = "canceled_user";
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
            []
        );
    }

}
