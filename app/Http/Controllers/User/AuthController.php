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
            'name' => ['required','string','max:255'],
            'email' => ['required','email','unique:users,email'],
            'phone' => ['required','string','numeric','digits:11','unique:users,phone'],
            'gender'=> ['required','string','max:10'],
            'password' => ['required_if:joined_with,1', // Required only if joined_with is 1
            'string','min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
            'confirmed'],
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
            'joined_with'=> $request->joined_with,
            'address'=> $request->address,
            'password' => (int) $request->joined_with === 1 ? Hash::make($request->password) : ((int) $request->joined_with === 2 ? Hash::make("Google") : Hash::make("Facebook")),
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
                ["joined_with" => [
                    '1 -> Sign Up with email',
                    '2 -> Sign Up with Google',
                    '3 -> Sign Up with Facebook'
                    ]
                    ]
            );
        }


    } 

    public function forgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => 'required|email',
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

        $user = User::where("email", $request->email)->first();


            if ($user) {
                $code = rand(100000, 999999);

                $user->email_last_verfication_code = Hash::make($code);
                $user->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Europe/Istanbul');
                $user->save();
    
    
                $msg_title = "Here's your Authentication Reset Password Code";
                $msg_content = "<h1>";
                $msg_content .= "Your Authentication Reset Password Dode is<span style='color: blue'>" . $code . "</span>";
                $msg_content .= "</h1>";
    
    
                $this->sendEmail($user->email, $msg_title, $msg_content);
    
    
                return $this->handleResponse(
                    true,
                    "Authentication Reset Code Sent To Your Email Successfully! ",
                    [],
                    [],
                    [
                        "code get expired after 10 minuts",
                        "the same endpoint you can use for ask resend email"
                    ]
                );
            }
            else {
                return $this->handleResponse(
                    false,
                    "",
                    ["This email is not used"],
                    [],
                    []
                );
            }
    }

    public function forgetPasswordCheckCode(Request $request) {
        $validator = Validator::make($request->all(), [
            "code" => ["required"],
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

        // This email request is coming from a hidden input type that referes to the previous page
        $user = User::where("email", $request->email)->first();
        $code = $request->code;

        if ($user) {
            if (!Hash::check($code, $user->email_last_verfication_code ? $user->email_last_verfication_code : Hash::make(0000))) {
                return $this->handleResponse(
                    false,
                    "",
                    ["Enter a Valid Code"],
                    [],
                    []
                );
            } else {
                $timezone = 'Europe/Istanbul'; // Replace with your specific timezone if different
                $verificationTime = new Carbon($user->email_last_verfication_code_expird_at, $timezone);
                if ($verificationTime->isPast()) {
                    return $this->handleResponse(
                        false,
                        "",
                        ["Code is Expired"],
                        [],
                        []
                    );
                } else {
                    if ($user) {
                        $passwordValidator = Validator::make($request->all(), [
                            "password" => 'required|string|min:12|
                            regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u
                            |confirmed',
                            ], [
                                "password.regex" => "Password must have Captial and small letters, and a special character",
                            ]);

                            if ($passwordValidator->fails()) {
                                return $this->handleResponse(
                                    false,
                                    "",
                                    [$validator->errors()->first()],
                                    [],
                                    []
                                );
                            }

                            $user->password = Hash::make($request->password);
                            $user->save();
                
                
                            return $this->handleResponse(
                                true,
                                "Password Changed Successfully",
                                [],
                                [],
                                []
                            );
                        }
                    }
                }
            }
        }
}
