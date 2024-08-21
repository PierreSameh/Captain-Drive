<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Ride;
use App\HandleTrait;
use App\Models\Offer;
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
            "st_lng"=> ["required","string"],
            "st_lat"=> ["required","string"],
            "en_lng"=> ["required","string"],
            "en_lat"=> ["required","string"],
            "stop_locations.*"=> ["nullable","array:stop_location,lng,lat"],
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
        if ($request->has("stop_locations")) {
            foreach ($request->stop_locations as $stop_location) {
    
                $stop = RideRequestStop::create([
                    "ride_request_id"=> $rideRequest->id,
                    'stop_location' => $stop_location['stop_location'],
                    'lng' => $stop_location['lng'],
                    'lat'=> $stop_location['lat']
                ]);

                $stopLocations[] = $stop;
            } 
        }
            $withStops = RideRequest::where('id', $rideRequest->id)->with('stops')->first();
            return $this->handleResponse(
                true,
                "Ride Request Sent Successfully",
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

    public function cancelRideRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            "ride_request_id"=> 'required'
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
        $ride = RideRequest::findOrFail($request->ride_request_id);
        if (isset($ride)) {
            $ride->status = "canceled";
            $ride->save();
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

    public function getAllOffersUser(Request $request) {
        $user = $request->user();
        $rideRequest = RideRequest::where("user_id", $user->id)
        ->where("status", "pending")
        ->first();
        if (isset($rideRequest)) {
            $offers = Offer::where('request_id', $rideRequest->id)
            ->whereNot('status', 'canceled')
            ->get();
            if(count($offers) > 0) {
            return $this->handleResponse(
                true,
                "Offers",
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

    public function getOfferUser(Request $request) {
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
        }
        $offer = Offer::with('request')->where('id', $request->offer_id)->first();
        if (isset($offer)) {
            if($offer->status == "canceled"){
                return $this->handleResponse(
                    false,
                    "Driver Canceled The Offer",
                    [],
                    [],
                    []
                );
            }
            return $this->handleResponse(
                true,
                'Offer',
                [],
                [
                    'offer' => $offer
                ],
                []
                );
            }
            return $this->handleResponse(
                true,
                'Offer Has Been Canceled',
                [],
                [],
                []
            );
    }

    public function acceptOfferUser(Request $request){
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
        }
        $user = $request->user();
        $offer = Offer::where('id', $request->offer_id)->first();
        $rideRequest = RideRequest::where('id', $offer->request_id)->first();
        if (isset($offer)) {
            $rideRequest->status = "closed";
            $rideRequest->save();

            $offer->status = "accepted";
            $offer->save();

            Ride::create([
                "offer_id" => $offer->id,
            ]);

            return $this->handleResponse(
                true,
                "Offer Accepted",
                [],
                [
                    "offer" => $offer,
                    "ride_request" => $rideRequest
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
    public function rejectOfferUser(Request $request){
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
        }
        $user = $request->user();
        $offer = Offer::where('id', $request->offer_id)->first();
        if (isset($offer)) {
            $offer->status = "rejected";
            $offer->save();

            return $this->handleResponse(
                true,
                "Offer Accepted",
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

    public function getRideUser(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_driver', 'canceled_user'])
        ->with(['offer.request', 'offer.request.stops'])
        ->first();
        if($ride){
            $canceled = $ride->where('status', 'canceled_driver');
            if($canceled){
                return $this->handleResponse(
                    false,
                    "Driver Canceled The Ride",
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

    public function cancelRideByUser(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_driver', 'canceled_user'])
        ->with(['offer.request'])
        ->first();
        if($ride){
        $ride->status = "canceled_user";
        $ride->save();
        return $this->handleResponse(
            false,
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
    public function setToDestination(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_driver', 'canceled_user'])
        ->where('status', 'arrived')
        ->with(['offer.request'])
        ->first();
        if($ride){
        $ride->status = "to_destination";
        $ride->save();
        return $this->handleResponse(
            true,
            "Your Ride Has Start! Enjoy Your Trip",
            [],
            [
                "ride" => $ride
            ],
            []
        );
        }
        return $this->handleResponse(
            false,
            "Driver Has Not Arrived Yet",
            [],
            [],
            []
        );
    }

    public function review(Request $request){
        $validator = Validator::make($request->all(),[
            "rate" => ["nullable", "numeric", "in:1,2,3,4,5"],
            "review" => ["nullable", "string", "max:1000"]
        ]);
        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "note"=>"This Function Rates The Latest Completed Passenger's Ride, It Can't be used with old rides",
                    "rate" => [
                        "1" => "1 star",
                        "2" => "2 stars",
                        "3" => "3 stars",
                        "4" => "4 stars",
                        "5" => "5 stars",
                    ]
                ]
            );
        }
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', 'completed')
        ->with(['offer.request', 'offer.request.stops'])
        ->latest()->first();
        if($ride){
            $ride->rate = $request->rate;
            $ride->review = $request->review;
            $ride->save();
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "ride" => $ride
                ],
                [
                    "This Function Rates The Latest Completed Passenger's Ride, It Can't be used with old rides",
                    "rate" => [
                        "1" => "1 star",
                        "2" => "2 stars",
                        "3" => "3 stars",
                        "4" => "4 stars",
                        "5" => "5 stars",
                    ]
                ]
            );
        }
        return $this->handleResponse(
            false,
            "Ride Not Found",
            [],
            [],
            [
                "This Function Rates The Latest Completed Passenger's Ride, It Can't be used with old rides",
                "rate" => [
                    "1" => "1 star",
                    "2" => "2 stars",
                    "3" => "3 stars",
                    "4" => "4 stars",
                    "5" => "5 stars",
                ]
            ]
        );
    }

    public function activities(Request $request){
        $userId = $request->user()->id;
        $activities = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
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
