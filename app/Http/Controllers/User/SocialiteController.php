<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\HandleTrait;

class SocialiteController extends Controller
{
    use HandleTrait;
    public function redirectToGoogle() {
        return Socialite::driver("google")->redirect();
    }
    public function redirectToFacebook() {
        return Socialite::driver("facebook")->redirect();
    }

    public function handleGoogleCallback(Request $request) {
        try {
            $user = Socialite::driver("google")->user();
            $findUser = User::where('social_id', $user->id)->first();
            if ($findUser) {
                Auth::login($findUser);
                $token = $findUser->createToken('token')->plainTextToken;
                return $this->handleResponse(
                    true,
                    "You are Loged In",
                    [],
                    [
                        $token,
                    ],
                    []
                ); 
            } else {
                $newUser = User::create([
                    'name'=> $user->name,
                    'email'=> $user->email,
                    'social_id'=> $user->id,
                    'social_type'=> 'google',
                    'password'=> Hash::make('my-google'),
                ]);

                Auth::login($newUser);
                $token = $newUser->createToken('token')->plainTextToken;
                return $this->handleResponse(
                    true,
                    "You are Loged In",
                    [],
                    [
                        $token,
                    ],
                    []
                );
            }

        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                "Error Signing U[",
                [$e->getMessage()],
                [],
                []
            );
        }
    }

    public function handleFacebookCallback(Request $request) {
        try {
            $user = Socialite::driver("facebook")->user();
            $findUser = User::where('social_id', $user->id)->first();
            if ($findUser) {
                Auth::login($findUser);
                $token = $findUser->createToken('token')->plainTextToken;
                return $this->handleResponse(
                    true,
                    "You are Loged In",
                    [],
                    [
                        $token,
                    ],
                    []
                ); 
            } else {
                $newUser = User::create([
                    'name'=> $user->name,
                    'email'=> $user->email,
                    'social_id'=> $user->id,
                    'social_type'=> 'facebook',
                    'password'=> Hash::make('my-facebook'),
                ]);

                Auth::login($newUser);
                $token = $newUser->createToken('token')->plainTextToken;
                return $this->handleResponse(
                    true,
                    "You are Loged In",
                    [],
                    [
                        $token,
                    ],
                    []
                );
            }

        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                "Error Signing U[",
                [$e->getMessage()],
                [],
                []
            );
        }
    }
}
