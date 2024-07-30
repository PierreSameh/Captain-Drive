<?php

namespace App\Http\Controllers\User;

use App\HandleTrait;
use App\SendMailTrait;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;






class AuthController extends Controller
{
    use HandleTrait, SendMailTrait;

    public function register(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|numeric|digits:11|unique:users,phone',
            'gender'=> 'required|string|max:10',
            'password' => 'required|string|min:8|
            regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u
            |confirmed',
        ], [
            "password.regex" => "Password must have Captial and small letters, and a special character",
        ]);


        if ($validator->fails()) {
                return $this->handleResponse(
                false,
                "Error Signing UP",
                [$validator->errors()],
                [],
                []
            );
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone'=> $request->phone,
            'gender'=> $request->gender,
            'address'=> $request->address,
            'password' => Hash::make($request->password),
        ]);



        $token = $user->createToken('token')->plainTextToken;




        return $this->handleResponse(
            true,
            "You are Signed Up",
            [],
            [
                $user,
                $token
            ],
            []
        );


        } catch (\Exception $e) {
            DB::rollBack();


            return $this->handleResponse(
                false,
                "Error Signing UP",
                [$e->getMessage()],
                [],
                []
            );
        }


    } 
}
