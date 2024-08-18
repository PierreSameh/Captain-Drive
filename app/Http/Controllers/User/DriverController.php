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
use App\Models\Driver;
use App\Models\DriverDoc;
use App\Models\Vehicle;
use App\Models\Wallet;
use App\Models\RejectMessage;
use Illuminate\Support\Str;


class DriverController extends Controller
{
    use HandleTrait, SendMailTrait;


    public function registerDriver(Request $request)
    {
        DB::beginTransaction();

        try {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','unique:drivers,email'],
            'phone' => ['required','string','numeric','digits:11','unique:drivers,phone',
            'unique:drivers,add_phone'],
            'add_phone' => ['required','string','numeric','digits:11','unique:drivers,add_phone'],
            'national_id' => ['required','numeric', 'digits:14'],
            'social_status'=> ['required','string'],
            'gender'=> ['required','string','max:10'],
            'picture'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'password' => ['required','string','min:8',
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
                []
            );
        }



        $picture = $request->file('picture')->store('/storage/docs', 'public');
        $superKey = 'D';
        $uniqueID = $this->generateUniqueNumericId(10);
        $exists = Driver::where('unique_id', $uniqueID)->first();
        while (isset($exists)) {
            $uniqueID = $this->generateUniqueNumericId(10);
        }

        $driver = Driver::create([
            "name"=> $request->name,
            'email' => $request->email,
            'phone'=> $request->phone,
            'add_phone'=> $request->add_phone,
            'national_id'=> $request->national_id,
            'social_status'=> $request->social_status,
            'gender'=> $request->gender,
            'super_key'=> $superKey,
            'unique_id'=> $uniqueID,
            'picture'=> $picture,
            'password' => Hash::make($request->password),
        ]);

        $docsValidator = Validator::make($request->all(), [
            'national_front'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'national_back'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'driverl_front'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'driverl_back'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'vehicle_front'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'vehicle_back'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
            'criminal_record'=> ['required','image','mimes:jpeg,png,jpg,gif','max:2048'],
        ]);


        if ($docsValidator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$docsValidator->errors()->first()],
                [],
                []
            );
        }

        $national_front = $request->file('national_front')->store('/storage/docs', 'public');
        $national_back = $request->file('national_back')->store('/storage/docs', 'public');
        $driverl_front = $request->file('driverl_front')->store('/storage/docs', 'public');
        $driverl_back = $request->file('driverl_back')->store('/storage/docs', 'public');
        $vehicle_front = $request->file('vehicle_front')->store('/storage/docs', 'public');
        $vehicle_back = $request->file('vehicle_back')->store('/storage/docs', 'public');
        $criminal_record = $request->file('criminal_record')->store('/storage/docs', 'public');

        $driverdoc = new DriverDoc();
        $driverdoc->driver_id = $driver->id;
        $driverdoc->national_front = $national_front;
        $driverdoc->national_back = $national_back;
        $driverdoc->driverl_front = $driverl_front;
        $driverdoc->driverl_back = $driverl_back;
        $driverdoc->vehicle_front = $vehicle_front;
        $driverdoc->vehicle_back = $vehicle_back;
        $driverdoc->criminal_record = $criminal_record;
        $driverdoc->save();

        $vehicleValidator = Validator::make($request->all(), [
            'type'=> ['required', 'numeric', 'digits:1'],
            'model'=> ['required','string','max:255'],
            'color'=> ['required','string','max:255'],
            'plates_number'=> ['required','string','max:255'],
        ]);

        if ($vehicleValidator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$vehicleValidator->errors()->first()],
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

        $vehicle = new Vehicle();
        $vehicle->driver_id = $driver->id;
        $vehicle->type = $request->type;
        $vehicle->model = $request->model;
        $vehicle->color = $request->color;
        $vehicle->plates_number = $request->plates_number;
        $vehicle->save();


        Wallet::create([
            'driver_id'=> $driver->id,
            'balance'=> 0,
        ]);

        $token = $driver->createToken('token')->plainTextToken;


        DB::commit();


        return $this->handleResponse(
            true,
            "You are Signed Up",
            [],
            [
                "driver" => $driver,
                "vehicle" => $vehicle,
                "token" => $token
            ],
            [
                "Vehicle Types" => [
                    '1 -> Car',
                    '2 -> conditioned car',
                    '3 -> Motorcycle',
                    '4 -> Taxi',
                    '5 -> Bus'
                ],
                "Driver Registration Creates a New Driver Wallet"
            ]
        );


        } catch (\Exception $e) {
            DB::rollBack();


            return $this->handleResponse(
                false,
                "Error Signing UP",
                [$e->getMessage()],
                [],
                [   
                    "Vehicle Types" => [
                    '1 -> Car',
                    '2 -> conditioned car',
                    '3 -> Motorcycle',
                    '4 -> Taxi',
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

    public function askEmailCodeDriver(Request $request) {
        $driver = $request->user();
        if ($driver) {
            $code = rand(1000, 9999);

            $driver->email_last_verfication_code = Hash::make($code);
            $driver->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Europe/Istanbul');
            $driver->save();

            $msg_title = "Here's your Authentication Code";
            $msg_content = "<h1>";
            $msg_content .= "Your Authentication code is<span style='color: blue'>" . $code . "</span>";
            $msg_content .= "</h1>";

            $this->sendEmail($driver->email, $msg_title, $msg_content);

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

    public function verifyEmailDriver(Request $request) {
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

    public function changePasswordDriver(Request $request) {
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


    public function sendForgetPasswordEmailDriver(Request $request) {
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

        $user = Driver::where("email", $request->email)->first();


            if ($user) {
                $code = rand(1000, 9999);

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

    public function forgetPasswordDriver(Request $request) {
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




        $user = Driver::where("email", $request->email)->first();
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


    public function forgetPasswordCheckCodeDriver(Request $request) {
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




        $user = Driver::where("email", $request->email)->first();
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

    public function loginDriver(Request $request) {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('driver')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $userManual = Driver::where('email', $request->email)->first();
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

    public function logoutDriver(Request $request) {
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

    public function getUserDriver(Request $request) {
        $user = $request->user();
        $data = Driver::with('vehicle', 'wallet')->where('id', $user->id)->first();
        if ($user) {
        return $this->handleResponse(
            true,
            "Driver Data",
            [],
            [
                "user" => $data
            ],
            []
            );
        }
        return $this->handleResponse(
            false,
            "Driver Not Found!",
            [],
            [],
            []
        );
     }

     public function editProfileDriver(Request $request) {
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
            $imagePath = $request->file('picture')->store('/storage/docs', 'public');
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

    public function getDriverForAdmin($driverId) {
        $driver = Driver::with('driverdocs', 'vehicle')->find($driverId);
        if ($driver) {
            return $this->handleResponse(
                true,
                '',
                [],
                [
                    "driver" => $driver
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Driver Not Found",
            [],
            [],
            []
            );
    }

    public function getAllUnapprovedDrivers() {
        $drivers = Driver::where('is_approved', 0)->paginate(20);
        if (count($drivers) > 0) {
            return $this->handleResponse(
                true,
                '',
                [],
                [
                    "drivers" => $drivers
                ],
                []
                );
        }
        return $this->handleResponse(
            false,
            "Drivers Requests Are Empty",
            [],
            [],
            []
            );
    }

    public function approveDriver($driverId) {
        $driver = Driver::with("driverdocs")->find($driverId);
        if ($driver) {
            $driver->is_approved = 1;
            $driver->save();
            return $this->handleResponse(
                true,
                'Driver Approved',
                [],
                [
                    "driver" => $driver
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Driver Not Found",
            [],
            [],
            []
            );
    }

    public function rejectDriver(Request $request, $driverId) {
        $driver = Driver::find($driverId);
        if ($driver) {
            $validator = Validator::make($request->all(), [
                "reasons"=> ["string", "max:1000"]
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
            $driver->is_approved = -1;
            $driver->save();
            $reject = new RejectMessage();
            $reject->driver_id = $driverId;
            $reject->reasons = $request->reasons;
            $reject->save();
            return $this->handleResponse(
                true,
                "Sorry, You Were Rejected!",
                [],
                [
                    "reasons"=> $reject->reasons
                ],
                []
                );
        }
            return $this->handleResponse(
                false,
                "Driver Not Found",
                [],
                [],
                []
                );
    }

    public function deleteDriverAfterReject(Request $request) {
        $driver = $request->user();
        $driverdata = Driver::where("id", $driver->id)->first();
        if ($driver) {
            $driver->delete();
            return $this->handleResponse(
                true,
                "Try Registering Again",
                [],
                [],
                []
                );
            }
            return $this->handleResponse(
                false,
                "Not Found",
                [],
                [],
                []
                );
    }

    public function setDriverStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            "status"=> ["required","string", "in:offline,online,busy"],
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
        if($driver) {
        $driver->status = $request->status;
        $driver->save();

        return $this->handleResponse(
            true,
            "",
            [],
            [
                "driver" => $driver,
            ],
            []
            );
        }
        return $this->handleResponse(
            false,
            "Driver Not Found",
            [],
            [],
            []
        );
    }
}
