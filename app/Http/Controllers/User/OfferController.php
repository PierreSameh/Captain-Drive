<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;
use App\Models\RideRequest;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;


class OfferController extends Controller
{
    use HandleTrait;
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
        $exists = Offer::where("request_id", $requestId)->first();
        if ($exists) {
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
}
