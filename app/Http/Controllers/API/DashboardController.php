<?php

namespace App\Http\Controllers\API;

use App\User;
use App\UserInfo;
use App\OtpVerification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function phoneNumber(Request $request){
        $messsages = [
            'mobile_number.required'=>'You cant leave mobile number field empty'
        ];
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required'],
        ],$messsages);
        if ($validator->fails()) {
            return response()->json(['status'=>'failed','errors'=>$validator->errors()]);
        }else{
            $newMobile = $request->mobile_number;
            if (Auth::user()) {
               $user = Auth::user();
               $usertableMobile = User::where('email',$user->email)->update(['mobile_number'=>$newMobile]);
               $otptableMobile = OtpVerification::where('mobile_number',$user->mobile_number)->update(['mobile_number'=>$newMobile]);
               if($usertableMobile && $otptableMobile){
                   $user_email = Auth::user()->mobile_number;
                return response()->json(['status'=>'success','message'=>'mobile number updated successfully.','mobile_number'=>$user_email]);
               }else{
                return response()->json(['status'=>'failed','message'=>'Something went wrong! Try again']);
               }
            }else{
                return response()->json(['status'=>'failed','message'=>'Unauthorized user !'], 401);
            }
        }
    }

    public function decoverySettings(Request $request){
        if (Auth::user()) {
            $user = Auth::user();
            $data = $request->all();
            $discovery_update = UserInfo::find($user->id);
            $discovery_update->intrest =  $data['interest'];
            $discovery_update->current_location =  $data['current_location'];
            $discovery_update->distance_range =  $data['distance_range'];
            $discovery_update->min_age =  $data['min_age'];
            $discovery_update->max_age =  $data['max_age'];
            $discovery_update->save();
            return response()->json(['status'=>'success','message'=>'setting updated successfully.']);
        }else{
            return response()->json(['status'=>'failed','message'=>'Unauthorized user !'], 401);
        }
    }


    public function pauseAccount(Request $request){
        if (Auth::user()) {
            $user = Auth::user();
            $data = $request->all();
            $user_status_update = User::find($user->id);
            $user_status_update->account_status =  $data['account_status'];
            $user_status_update->save();
            return response()->json(['status'=>'success','message'=>'Your account status has been updated.']);
        }else{
            return response()->json(['status'=>'failed','message'=>'Unauthorized user !'], 401);
        }
    }
}
