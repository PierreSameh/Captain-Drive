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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use HandleTrait, SendMailTrait;

    public function register(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'email' => ['required_unless:joined_with,3','email','unique:users,email'],
            'phone' => ['required_if:joined_with,1',
            'string','numeric','digits:11','unique:users,phone'],
            'gender'=> ['required','string','max:10'],
            'joined_with'=> ['required', 'in:1,2,3'],
            'password' => ['required_if:joined_with,1',
            'string','min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
            'confirmed'],
        ], [
            "password.regex" => "Password must have Captial and small letters, and a special character",
        ]);


        if ($validator->fails()) {
                return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "joined_with" => [
                        "1"=> "Signed Up by email",
                        "2"=> "With Google",
                        "3"=> "With Facebook",
                    ]
                ]
            );
        }

        $superKey = "P";
        $uniqueID = $this->generateUniqueNumericId(10);
        $exists = User::where('unique_id', $uniqueID)->first();
        while (isset($exists)) {
            $uniqueID = $this->generateUniqueNumericId(10);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone'=> $request->phone,
            'joined_with'=> $request->joined_with,
            'super_key'=> $superKey,
            'unique_id'=> $uniqueID,
            'gender'=> $request->gender,
            'address'=> $request->address,
            'password' => (int) $request->joined_with === 1 ? Hash::make($request->password) : ((int) $request->joined_with === 2 ? Hash::make("Google") : Hash::make("Facebook")),
        ]);



        $token = $user->createToken('token')->plainTextToken;




        return $this->handleResponse(
            true,
            "You are Signed Up",
            [],
            [
                "user" => $user,
                "token" => $token
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
    private function generateUniqueNumericId($length)
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }
    // private function generateUniqueId($length)
    // {
    //     return Str::random($length);
    // }

    public function askEmailCode(Request $request) {
        $user = $request->user();
        if ($user) {
            $code = rand(1000, 9999);

            $user->email_last_verfication_code = Hash::make($code);
            $user->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Africa/Cairo');
            $user->save();

            $msg_title = "Here's your Authentication Code";
            $msg_content = "<h1>";
            $msg_content .= "Your Authentication code is<span style='color: blue'>" . $code . "</span>";
            $msg_content .= "</h1>";

            $this->sendEmail($user->email, $msg_title, $msg_content);

            return $this->handleResponse(
                true,
                "Authentication Code Sent To Your Email Successfully! ",
                [],
                [],
                [
                    "code get expired after 10 minuts",
                    "the same endpoint you can use for ask resend email"
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["invalid process"],
            [],
            [
                "code get expired after 10 minuts",
                "the same endpoint you can use for ask resend email"
            ]
        );
    }

    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            "code" => ["required"],
        ], [
            "code.required" => "Enter Authentication Code",
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

        $user = $request->user();
        $code = $request->code;

        if ($user) {
            if (!Hash::check($code, $user->email_last_verfication_code ? $user->email_last_verfication_code : Hash::make(0000))) {
                return $this->handleResponse(
                    false,
                    "",
                    ["Incorrect Code"],
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
                    $user->is_email_verified = true;
                    $user->save();




                    if ($user) {
                        return $this->handleResponse(
                            true,
                            "Your Email is Verifyied",
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        }
    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "old_password" => 'required',
            'password' => 'required|string|min:8|
            regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u
            |confirmed',
            ], [
            "password.regex" => "Password must have Captial and small letters, and a special character",
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

        $user = $request->user();
        $old_password = $request->old_password;

        if ($user) {
            if (!Hash::check($old_password, $user->password)) {
                return $this->handleResponse(
                    false,
                    "",
                    ["Current Password is Incorrect"],
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


    public function sendForgetPasswordEmail(Request $request) {
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
                $code = rand(1000, 9999);

                $user->email_last_verfication_code = Hash::make($code);
                $user->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Africa/Cairo');
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

    public function forgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
                'confirmed'
            ],
        ], [
            "email.required" => "من فضلك ادخل بريدك الاكتروني ",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
            "password.required" => "ادخل كلمة المرور",
            "password.min" => "يجب ان تكون كلمة المرور من 8 احرف على الاقل",
            "password.regex" => "يجب ان تحتوي كلمة المرور علي حروف وارقام ورموز",
            "password.confirmed" => "كلمة المرور والتاكيد غير متطابقان",
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
        $code = $request->code;


        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();


            if ($user) {
                return $this->handleResponse(
                    true,
                    "تم تعين كلمة المرور بنجاح ",
                    [],
                    [],
                    []
                );
            }
        }
        else {
            return $this->handleResponse(
                false,
                "",
                ["هذا الحساب غير مسجل"],
                [],
                []
            );
        }


    }


    public function forgetPasswordCheckCode(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            "code" => ["required"],
        ], [
            "code.required" => "ادخل رمز التاكيد ",
            "email.required" => "من فضلك ادخل بريدك الاكتروني ",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
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
        $code = $request->code;


        if ($user) {
            if (!Hash::check($code, $user->email_last_verfication_code ? $user->email_last_verfication_code : Hash::make(0000))) {
                return $this->handleResponse(
                    false,
                    "",
                    ["الرمز غير صحيح"],
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
                        ["الرمز غير ساري"],
                        [],
                        []
                    );
                } else {
                    if ($user) {
                        return $this->handleResponse(
                            true,
                            "الرمز صحيح",
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا الحساب غير مسجل"],
                [],
                []
            );
        }


    }



    
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $userManual = Auth::user();
            $token = $userManual->createToken('token')->plainTextToken;


        }else  {
                return $this->handleResponse(
                false,
                "Error Signing UP",
                ['Invalid Credentials'],
                [],
                []
            );
        }


        // return response()->json(compact('token'));
        return $this->handleResponse(
            true,
            "You are Loged In",
            [],
            [
               "token" => $token,
            ],
            []
        );
    }

    public function logout(Request $request) {
        $user = $request->user();


        if ($user) {
            if ($user->tokens())
                $user->tokens()->delete();
        }


        return $this->handleResponse(
            true,
            "Loged Out",
            [],
            [
            ],
            [
                "On logout" => "كل التوكينز بتتمسح انت كمان امسحها من الكاش عندك"
            ]
        );
    }

    public function getUser(Request $request) {
        $user = $request->user();
        if ($user) {
        return $this->handleResponse(
            true,
            "User Data",
            [],
            ["user" => $user],
            []
            );
        }
        return $this->handleResponse(
            false,
            "User Not Found!",
            [],
            [],
            []
        );
     }

     public function editProfile(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','unique:users,email'],
            'phone' => ['required','string','numeric','digits:11','unique:users,phone'],
            'picture'=> ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048']
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
        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->picture) {
            $imagePath = $request->file('picture')->store('/storage/profile', 'public');
            $user->picture = $imagePath;
        }

        $user->save();
        
        return $this->handleResponse(
            true,
            "Info Updated Successfully",
            [],
            [
               "user" => $user,
            ],
            []
        );
        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                "Coudln't Edit Your Info",
                [$e->getMessage()],
                [],
                []
            );
        }
        
    }


}
