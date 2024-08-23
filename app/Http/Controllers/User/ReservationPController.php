<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use Illuminate\Support\Facades\DB;
use App\Models\ReservationRequest;
use App\Models\ReservationStop;
use App\Models\ReservationOffer;
use App\Models\Reservation;


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
        $st_lng = $request->st_lng;
        $st_lat = $request->st_lat;
        $en_lng = $request->en_lng;
        $en_lat = $request->en_lat;
        if ($user && $st_lng && $st_lat && $en_lng && $en_lat){
            $reservationRequest = ReservationRequest::create([
                "user_id"=> $user->id,
                "vehicle"=> $request->vehicle,
                "st_location"=> $request->st_location,
                "en_location"=> $request->en_location,
                "st_lng"=> $st_lng,
                "st_lat"=> $st_lat,
                "en_lng"=> $en_lng,
                "en_lat"=> $en_lat,
                "time"=> $request->time
            ]);
            $stopLocations = [];
        if ($request->has("stop_locations")) {
            foreach ($request->stop_locations as $stop_location) {
    
                $stop = ReservationStop::create([
                    "reservation_request_id"=> $reservationRequest->id,
                    'stop_location' => $stop_location['stop_location'],
                    'lng' => $stop_location['lng'],
                    'lat'=> $stop_location['lat']
                ]);

                $stopLocations[] = $stop;
            } 
        }
            $withStops = ReservationRequest::where('id', $reservationRequest->id)->with('stops')->first();
            return $this->handleResponse(
                true,
                "Reservation Request Sent Successfully",
                [],
                [
                    "Request" => $withStops,
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
        $ride = ReservationRequest::where("user_id", $user->id)->with('stops')->latest()->limit(1)->get();
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
        $ride = ReservationRequest::findOrFail($request->reservation_request_id);
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
        $rideRequest = ReservationRequest::where("user_id", $user->id)
        ->where("status", "pending")
        ->first();
        if (isset($rideRequest)) {
            $offers = ReservationOffer::where('reservation_request_id', $rideRequest->id)
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->with('driver')
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
                "No Offers",
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Request Not Available",
            [],
            [],
            []
            );
    }

    public function acceptReservationOffer(Request $request){
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
        $offer = ReservationOffer::where('id', $request->reservation_offer_id)->first();
        $rideRequest = ReservationRequest::where('id', $offer->reservation_request_id)->first();
        if (isset($offer)) {
            $rideRequest->status = "closed";
            $rideRequest->save();

            $offer->status = "accepted";
            $offer->save();

            Reservation::create([
                "reservation_offer_id" => $offer->id,
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
            return $this->handleResponse(
                false,
                "Offer Not Found",
                [],
                [],
                []
            );

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
        $offer = ReservationOffer::where('id', $request->reservation_offer_id)
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
                "Offer Not Found",
                [],
                [],
                []
            );

    }

    public function getReservation(Request $request){
        $userId = $request->user()->id;
        $ride = Reservation::whereHas('reservationOffer.reservationRequest', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user'])
        ->with(['reservationOffer.reservationRequest', 'reservationOffer.reservationRequest.stops', 'reservationOffer.driver'])
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
            "No Active Rides",
            [],
            [],
            []
        );
        
        
    }

    public function cancelReservation(Request $request){
        $userId = $request->user()->id;
        $ride = Reservation::whereHas('reservationOffer.reservationRequest', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['reservationOffer.reservationRequest', 'reservationOffer.driver'])
        ->latest()->first();
        if($ride){
        $ride->status = "canceled_user";
        $ride->save();
        return $this->handleResponse(
            false,
            "Reservation Canceled",
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
