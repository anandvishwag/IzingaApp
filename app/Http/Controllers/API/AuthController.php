<?php

namespace App\Http\Controllers\API;

use App\User;
use App\UserInfo;
use App\UserMedia;
use App\OtpVerification;
use Illuminate\Http\Request;
use Nexmo\Laravel\Facade\Nexmo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function NewgenerateOTP(){
        $otp = mt_rand(1000,9999);
        return $otp;
    }

    public function generateOTP(){
        $otp = mt_rand(1000,9999);
        return $otp;
    }
    public function sendOTP(Request $request){
        $messsages = [
            'mobile_number.required'=>'You cant leave mobile number field empty',
            'country_code.required'=>'You cant leave country code empty',
        ];
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required'],
            'country_code' => ['required'],
        ],$messsages);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }else{
            $now = date('Y-m-d h:i:s');
            $otp = $this->generateOTP();
            $nowDate = date('Y-m-d');
            $nowTime = date('H:i:s');
            $endTime = strtotime("+15 minutes", strtotime($nowTime));
            $endtime = date('h:i:s', $endTime);
            $endtime = $nowDate.' '.$endtime;
            $existMobile  = OtpVerification::where(['mobile_number'=>$request->mobile_number])->first();
            if($existMobile){
                    $newMobileRegister = OtpVerification::find($existMobile->id);
                    $newMobileRegister->otp = $otp;
                    $newMobileRegister->otp_expry = $endtime;
                    $newMobileRegister->created_at = $now;
                    $newMobileRegister->updated_at = $now;
                    if($newMobileRegister->save()){
                        Nexmo::message()->send([
                            'to' => $request->country_code.$request->mobile_number,
                            'from' => 'Izinga Signup',
                            'text' => "Your one time password for Izinga Registration is : $otp"
                        ]);
                    return response()->json(['success'=>true,'otp'=>$otp,'mobile_number'=>$request->mobile_number], 200);
                }

            }else{
                $newMobileRegister = new OtpVerification();
                $newMobileRegister->mobile_number = $request->mobile_number;
                $newMobileRegister->otp = $otp;
                $newMobileRegister->otp_expry = $endtime;
               if($newMobileRegister->save()){
                Nexmo::message()->send([
                    'to' => $request->country_code.$request->mobile_number,
                    'from' => 'Izinga Signup',
                    'text' => "Your one time password for Izinga Registration is : $otp"
                ]);
                return response()->json(['success'=>true,'otp'=>$otp,'mobile_number'=>$request->mobile_number], 200);
               }
            }


        }
    }

    public function verifyOTP(Request $request){
        $messsages = [
            'mobile_number.required'=>'You cant leave mobile number field empty',
            'otp.required'=>'Please enter your OTP ! You cant leave empty.',
        ];
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required'],
            'otp' => ['required'],
        ],$messsages);
        if ($validator->fails()) {
            return response()->json(['status'=>'failed','errors'=>$validator->errors()]);
        }else{
            $data = $request->all();
            $getOTP  = OtpVerification::where(['mobile_number'=>$data['mobile_number'],'otp'=>$data['otp']])->first();
            if($getOTP){
                $otpExpry = $getOTP->otp_expry;
                $now = date('Y-m-d H:i:s');
                if($otpExpry > $now){
                   if($getOTP->isVerified == 0){
                    $isVerified = OtpVerification::where(['mobile_number'=>$data['mobile_number'],'otp'=>$data['otp']])->update(['isVerified'=>1,'verification_date'=>$now]);
                    if($isVerified){
                        return response()->json(['status'=>'success','message'=>'Mobile number has been verified.'], 200);
                    }
                   }else{
                    // Get user record
                    $user = User::where('mobile_number', $data['mobile_number'])->first();
                    if($user){
                        Auth::login($user);
                        if (Auth::user()) {
                            $token =  $user->createToken($user->email)->accessToken;
                            return response()->json(['status'=>'success','message'=>'User successfully loged in.','user_data'=>$user, 'token'=>$token], 200);
                        }
                    }else{
                        return response()->json(['status'=>'success','message'=>'User mobile verified ! Please register now .'], 200);
                    }

                  //  return response()->json(['status'=>'success','message'=>'Mobile already verified.'], 200);
                   }

                }else{
                    return response()->json(['status'=>'invalid','error'=>'OTP is expired.']);
                }

            }else{
                return response()->json(['status'=>'invalid','error'=>'Please enter a valid OTP.']);
            }

        }

    }


    public function resister(Request $request){
        $picture_array = [];
        $messsages = [
            'fname.required'=>'You cant leave first name field empty',
            'lname.required'=>'You cant leave last name field empty',
            'email.required'=>'You cant leave email field empty',
            'birthday.required'=>'You cant leave birthday field empty',
            'intrest.required'=>'You cant leave intrest field empty',
            'gender.required'=>'You cant leave gender field empty',
            'city.required'=>'You cant leave city field empty',
            'bio.required'=>'You cant leave bio field empty',
        ];
        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|unique:users',
            'mobile_number' => 'required|unique:users',
            'birthday' => 'required',
            'intrest' => 'required',
            'gender' => 'required',
            'city' => 'required',
            'bio' => 'required',
            'profile_pictures' => 'required',
        ],$messsages);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }else{
            if ($request->hasfile('profile_pictures')) {
                  foreach ($request->file('profile_pictures') as $profile_picture) {
                    $profile_picture_name = explode('.', $profile_picture->getClientOriginalName())[0];
                    $picture = str_replace(' ', '', $profile_picture_name) . '_' . time() . '.' . $profile_picture->extension();
                    $profile_picture->move(public_path('/uploads/profile_pictures/'), $picture);
                    $picture_array[] = asset('/uploads/profile_pictures/' . $picture);
                }

            }
            $data = $request->all();
            $getVerifiedUser = OtpVerification::where(['mobile_number'=>$data['mobile_number'],'isVerified'=>1])->first();
           if($getVerifiedUser){

            $user = new User();
            $user->mobile_number = $data['mobile_number'];
            $user->email = $data['email'];
            if($user->save()){
                $user_info = new UserInfo();
                $user_info->user_id = $user->id;
                $user_info->fname = $data['fname'];
                $user_info->lname = $data['lname'];
                $user_info->birthday = $data['birthday'];
                $user_info->intrest = $data['intrest'];
                $user_info->gender = $data['gender'];
                $user_info->city = $data['city'];
                $user_info->bio = $data['bio'];
                $user_info->save();
                if(!is_null($picture_array)){
                    foreach($picture_array as $user_picture){
                        DB::table('user_medias')->insert(['user_id'=>$user->id,'pictures'=>$user_picture]);
                    }
                }
                Auth::login($user);
                if (Auth::user()) {
                    $token =  $user->createToken($user->email)->accessToken;
                    return response()->json(['status'=>'success','message'=>'User created successfully.','user_data'=>$user, 'token'=>$token], 200);
                }
            }else{
                return response()->json(['status'=>'failed','message'=>'Some thing went wrong in insertion of data.'], 400);
            }
           }else{
            return response()->json(['status'=>'failed','message'=>'User mobile not verified'], 400);
           }
        }
    }


    public function logout(Request $request)
    {
        foreach($request->user()->tokens as $token)
            $token->revoke();

        return ['status' => true, 'message' => 'Tokens revoked successfully.'];
    }//..... end of logout() .....//

    public function sendSms(Request $request)
    {
       // dd($request->mobile);
        // return $request;
        $otp = "4049";
        Nexmo::message()->send([
            'to' => '91'.$request->mobile,
            'from' => 'Izinga Signup',
            'text' => "Your one time password for Izinga Registration is : $otp"
        ]);
        dd('SMS Send Please checkn !');
    }

}


