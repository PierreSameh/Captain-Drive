<?php

namespace App\Http\Controllers\User;

use App\HandleTrait;
use App\SendMailTrait;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;
use App\Models\Driver;
use Illuminate\Support\Facades\Validator;


class WalletController extends Controller
{
    use HandleTrait;

    public function addWallet(Request $request) {
        $driver = $request->user();
        $exists = Wallet::where("driver_id", $driver->id)->first();
        if (!isset($exists)) {
        $wallet = new Wallet();
        $wallet->driver_id = $driver->id;
        $wallet->balance= 0;
        $wallet->save();
        return $this->handleResponse(
            true,
            "Wallet Created Successfully",
            [],
            [
                "driver" => $driver,
                "wallet" => $wallet,
            ],
            []
        );
       } else {
        return $this->handleResponse(
            false,
            "Driver Already Has A Wallet",
            [],
            [],
            []
        );
       }
    }

    public function editWallet(Request $request) {
        $validator = Validator::make($request->all(), [
            "balance"=> ["required", "numeric"],
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
        $wallet = Wallet::where("driver_id", $driver->id)->first();
        if (isset($wallet)) {
            $wallet->balance = $wallet->balance + $request->balance;
            $wallet->save();

            return $this->handleResponse(
                true,
                "Balance Changed",
                [],
                [
                    "wallet" => $wallet
                ],
                []
                );
        } else {
            return $this->handleResponse(
                false,
                "Driver Does not Have A Wallet",
                [],
                [],
                []
            );
        }
    }

    public function getWallet(Request $request) {
        $driver = $request->user();
        $wallet = Wallet::where("driver_id", $driver->id)->first();
        if (isset($wallet)) {
            return $this->handleResponse(
                true,
                "Driver's Wallet",
                [],
                [
                    "wallet" => $wallet
                ],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "Driver Does Not Have a Wallet",
                [],
                [],
                []
            );
        }
    }

    public function deleteWallet(Request $request) {
        $driver = $request->user();
        $wallet = Wallet::where("driver_id", $driver->id)->first();
        if (isset($wallet)) {
            $wallet->delete();
            return $this->handleResponse(
                true,
                "Wallet Deleted Successfully",
                [],
                [],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "Driver Does Not Have a Wallet",
                [],
                [],
                []
            );
        }

    }
}
