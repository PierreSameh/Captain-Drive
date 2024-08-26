<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\HandleTrait;
use App\Models\Report;


class ReportController extends Controller
{
    use HandleTrait;
    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            "captain_name"=> ["required", "string","max:255"],
            "vehicle_plate"=> ["required","string","max:255"],
            "ride_date"=>["required","date_format:Y-m-d H:i:s"],
            "report"=>["required","string","max:1000"],
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
        $report = Report::create([
            "user_id"=> $user->id,
            "captain_name"=> $request->captain_name,
            "vehicle_plate"=> $request->vehicle_plate,
            "ride_date"=> $request->ride_date,
            "report"=> $request->report,
        ]);
        if($report){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "report" => $report
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "",
            [],
            [],
            []
        );
    }
}
