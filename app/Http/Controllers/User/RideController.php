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
use App\Models\Profit;

class RideController extends Controller
{
    use HandleTrait;

    // public function sendRideRequest(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         "vehicle"=> ["required", "numeric", "in:1,2,3,4,5"],
    //         "st_location"=> ["required","string","max:255"],
    //         "en_location"=> ["required","string","max:255"],
    //         "st_lng"=> ["required","string"],
    //         "st_lat"=> ["required","string"],
    //         "en_lng"=> ["required","string"],
    //         "en_lat"=> ["required","string"],
    //         "stop_locations.*"=> ["nullable","array:stop_location,lng,lat"],
    //     ]);
        
    //     if ($validator->fails()){
    //         return $this->handleResponse(
    //             false,
    //             "",
    //             [$validator->errors()->first()],
    //             [],
    //             [
    //                 "Vehicle Types" => [
    //                     '1 -> Car',
    //                     '2 -> conditioned car',
    //                     '3 -> Motorcycle',
    //                     '4 -> Taxi',
    //                     '5 -> Bus'
    //                     ]
    //             ]
    //         );
    //     }

    //     $user = $request->user();
    //     $st_lng = $request->st_lng;
    //     $st_lat = $request->st_lat;
    //     $en_lng = $request->en_lng;
    //     $en_lat = $request->en_lat;
    //     if ($user && $st_lng && $st_lat && $en_lng && $en_lat){
    //         $rideRequest = RideRequest::create([
    //             "user_id"=> $user->id,
    //             "vehicle"=> $request->vehicle,
    //             "st_location"=> $request->st_location,
    //             "en_location"=> $request->en_location,
    //             "st_lng"=> $st_lng,
    //             "st_lat"=> $st_lat,
    //             "en_lng"=> $en_lng,
    //             "en_lat"=> $en_lat
    //         ]);
    //         $stopLocations = [];
    //     if ($request->has("stop_locations")) {
    //         foreach ($request->stop_locations as $stop_location) {
    
    //             $stop = RideRequestStop::create([
    //                 "ride_request_id"=> $rideRequest->id,
    //                 'stop_location' => $stop_location['stop_location'],
    //                 'lng' => $stop_location['lng'],
    //                 'lat'=> $stop_location['lat']
    //             ]);

    //             $stopLocations[] = $stop;
    //         } 
    //     }
    //         $withStops = RideRequest::where('id', $rideRequest->id)->with('stops')->first();
    //         return $this->handleResponse(
    //             true,
    //             "Ride Request Sent Successfully",
    //             [],
    //             [
    //                 "Request" => $withStops,
    //             ],
    //             []
    //         );
    //     }
    //     return $this->handleResponse(
    //         false,
    //         "Can't Get Location",
    //         [],
    //         [],
    //         ["Enter lng & lat data correctly"]
    //     );
    // }


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
            "price"=>["required", "numeric"],
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
    
        if ($user && $st_lng && $st_lat && $en_lng && $en_lat) {
    
            // Haversine formula to calculate distance in kilometers
            $earthRadius = 6371; // Earth's radius in kilometers
    
            $dLat = deg2rad($en_lat - $st_lat);
            $dLng = deg2rad($en_lng - $st_lng);
    
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($st_lat)) * cos(deg2rad($en_lat)) *
                 sin($dLng / 2) * sin($dLng / 2);
    
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
            $distance = $earthRadius * $c; // Distance in kilometers
    
            // Assume the price per kilometer is stored in a variable or a config

            // Save the ride request
            $rideRequest = RideRequest::create([
                "user_id"=> $user->id,
                "vehicle"=> $request->vehicle,
                "st_location"=> $request->st_location,
                "en_location"=> $request->en_location,
                "st_lng"=> $st_lng,
                "st_lat"=> $st_lat,
                "en_lng"=> $en_lng,
                "en_lat"=> $en_lat,
            ]);

            $profit = Profit::first();
            if($profit){
            $pricePerKilometer = $profit->per_kilo; // Example price, replace with actual value
    
            $totalPrice = $distance * $pricePerKilometer;
            $rideRequest->distance = $distance;
            $rideRequest->price = $request->price;
            $rideRequest->save();
            }

            // Process stop locations
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
        return $this->handleResponse(
            false,
            "No Ride Reqeusts Found",
            [],
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
        $ride = RideRequest::findOrFail($request->ride_request_id);
        if (isset($ride)) {
            $ride->status = "canceled";
            $ride->save();
            return $this->handleResponse(
                true,
                "Ride Cancelled",
                [],
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
        return $this->handleResponse(
            false,
            "Can't Find The Ride",
            [],
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

    public function getAllOffersUser(Request $request) {
        $user = $request->user();
        $rideRequest = RideRequest::where("user_id", $user->id)
        ->where("status", "pending")->
        latest()->first();
        if (isset($rideRequest)) {
            $offers = Offer::where('request_id', $rideRequest->id)
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->with(['driver.vehicle'])
            ->get();
            if(count($offers) > 0) {
            return $this->handleResponse(
                true,
                "Offers",
                [],
                [
                    "offers" => $offers
                ],
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
            return $this->handleResponse(
                true,
                "No Offers",
                [],
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
        return $this->handleResponse(
            false,
            "Request Not Available",
            [],
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
        $offer = Offer::with('request')->where('id', $request->offer_id)->with('driver')->first();
        if (isset($offer)) {
            if($offer->status == "canceled"){
                return $this->handleResponse(
                    false,
                    "Driver Canceled The Offer",
                    [],
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
            return $this->handleResponse(
                true,
                'Offer',
                [],
                [
                    'offer' => $offer
                ],
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
            return $this->handleResponse(
                true,
                'Offer Has Been Canceled',
                [],
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
        $offer = Offer::where('id', $request->offer_id)->with('driver')->first();
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
            return $this->handleResponse(
                false,
                "Offer Not Found",
                [],
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
        $offer = Offer::where('id', $request->offer_id)->first();
        if (isset($offer)) {
            $offer->status = "rejected";
            $offer->save();

            return $this->handleResponse(
                true,
                "Offer Rejected",
                [],
                [
                    "offer" => $offer,
                ],
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
            return $this->handleResponse(
                false,
                "Offer Not Found",
                [],
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

    public function getRideUser(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user'])
        ->with(['offer.request', 'offer.request.stops', 'offer.driver'])
        ->latest()->first();
        if($ride){
            if($ride->status == 'canceled_driver'){
                return $this->handleResponse(
                    false,
                    "Driver Canceled The Ride",
                    [],
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
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "ride" => $ride
                ],
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
        return $this->handleResponse(
            true,
            "No Active Rides",
            [],
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

    public function cancelRideByUser(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_user', 'canceled_driver'])
        ->with(['offer.request', 'offer.driver'])
        ->latest()->first();
        if($ride){
        $ride->status = "canceled_user";
        $ride->save();
        return $this->handleResponse(
            true,
            "Ride Canceled",
            [],
            [
                "ride" => $ride
            ],
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
        return $this->handleResponse(
            false,
            "Ride Not Found",
            [],
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
    public function setToDestination(Request $request){
        $userId = $request->user()->id;
        $ride = Ride::whereHas('offer.request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('status', ['completed', 'canceled_driver', 'canceled_user'])
        ->where('status', 'arrived')
        ->with(['offer.request', 'offer.driver'])
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
        return $this->handleResponse(
            false,
            "Driver Has Not Arrived Yet",
            [],
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
             // Calculate the new average rating for the driver
            $averageRating = Ride::whereHas('offer.driver', function($q) use ($ride){
                $q->where('driver_id', $ride->offer->driver_id);
            })
            ->whereNotNull('rate') // Only consider rides that have a rating
            ->avg('rate');

            // Update the driver's rate column
            $driver = $ride->offer->driver;
            $driver->rate = $averageRating;
            $driver->save();
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
        return $this->handleResponse(
            true,
            "You Have No Activities Yet",
            [],
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

}
