<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profit;
use App\HandleTrait;

class ProfitController extends Controller
{
    use HandleTrait;


    public function getAll(){
        $profits = Profit::all();
        if (count( $profits ) > 0) {
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "profits"=> $profits
            ],
            []
        );
        }
        return $this->handleResponse(
            true,
            "Admin Didn't Add any Profits yet",
            [],
            [],
            []
            );
    }
}
