<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\BaseController;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\Temp;
use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Http\Request;
use DateTime;
use Exception;
use Helper;
use Validator;

class AuthController extends BaseController
{
    // SEND OTP FOR REGISTRATION, LOGIN, RESEND OTP

    public function sendOtp(Request $request){
        try{
            
            $otp    = substr(number_format(time() * rand(),0,'',''),0,4);
            $data   = [];
            $data['is_user_exist'] = 0;
            $data['otp'] = (int)$otp;

            if(isset($request->email)){
                $validateData = Validator::make($request->all(), [
                    // 'email' => 'required|email|unique:users,email',
                    'email' => 'required|email',
                ]);

                if ($validateData->fails()) {
                    return $this->error($validateData->errors(),'Validation error',403);
                } 
                
                $key          = $request->email;
                $email_data   = [
                    'email'   => $key,
                    'otp'     => $otp,
                    'subject' => 'Email OTP Verification - For Meet now',
                ];

                Helper::sendMail('emails.email_verify', $email_data, $key, '');

                if (User::where('email', '=', $key)->count() > 0) {
                    $data['is_user_exist'] = 1;
                }
                $data['send_in'] = 'email';

            } else if(isset($request->phone_no)){

                $validateData = Validator::make($request->all(), [
                    // 'phone_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone_no',
                    'phone_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]);

                if ($validateData->fails()) {
                    return $this->error($validateData->errors(),'Validation error',403);
                } 
               
                $key             = $request->phone_no;

                if (User::where('phone_no','=', $key)->count() > 0) {
                    $data['is_user_exist'] = 1;
                }
                $data['send_in'] = 'phone_no';
            } else {
                return $this->error('Please enter email or phone number','Required parameter');
            }
            
            $temp         = Temp::firstOrNew(['key' => $key]);
            $temp->key    = $key;
            $temp->value  = $otp;
            $temp->save();
            
            return $this->success($data,'OTP send successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // VERIFY OTP (IF USER EXISTS AND OTP VERIFIED THEN IT IS USED AS A LOGIN) 

    public function verifyOtp(Request $request){
        // $user = User::where('email', '=', $request->email_or_phone)
        // ->orWhere('phone_no','=', $request->email_or_phone)
        // ->select('id','email', 'phone_no')
        // ->first();
        // $data['token'] = $user->createToken('Auth token')->accessToken;
        // return $data;
        try{
            $validateData = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'otp' => 'required',
                'type' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
            
            $temp         = Temp::where('key',$request->email_or_phone)->first();
            if($temp != null){
                $is_data_present = Temp::where('key',$request->email_or_phone)->where('value',$request->otp)->first();
                if($is_data_present != null){

                    $is_data_present->delete();
                    $data = [];
                    $data['user_id'] = 0;
                    $data['is_user_exist'] = 0;
                    $data['is_email_verified'] = 0;
                    $data['otp'] = (int)$request->otp;


                    // When user update email and come to verify screen at that time it is required to send id
                    if(isset($request->id)){
                        $user = User::where('id','=', $request->id)
                        ->select('id','email', 'phone_no','email_verified')
                        ->first();
                        if ($user && filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {
                            $user->update(['email'=> $request->email_or_phone]);
                        }
                    }

                    $user = User::where('email', '=', $request->email_or_phone)
                            ->orWhere('phone_no','=', $request->email_or_phone)
                            ->select('id','email', 'phone_no','email_verified')
                            ->first();

                    if ($user) {
                        $data['is_user_exist'] = 1;
                        $data['user_id'] = $user->id;
                        $data['email'] = $user->email;
                        
                        if ($user->email == $request->email_or_phone) {
                            $user->email_verified = 1;
                        }
                        $user->otp_verified = 1;
                        $user->save();
                        
                        // When user register and from the page where otp verifiy for email kill app and then try to do login so need to send email and verified 0.
                        // If user is exists and email not verifiy then show email send otp screen
                        $data['is_email_verified'] = $user->email_verified;

                        if($user->email_verified == 0){
                            $request1 = new Request();
                            $request1->merge(['email' => $user->email]);
                            $response = $this->sendOtp($request1);
                            $data11 = json_decode($response->getContent(), true);  
                            if ($data11 && isset($data11['data']['otp'])) {
                                $data['otp'] = (int)$data11['data']['otp'];  
                            } 
                        }

                        if($user->email_verified == 1 && $user->phone_verified = 1 && $user->otp_verified == 1 && $request->type != 'edit'){
                            $user->tokens()->delete();
                            $data['token'] = $user->createToken('Auth token')->accessToken;
                        }
                        
                        if($user->email_verified == 1 && $user->phone_verified = 1 && $user->otp_verified == 1 && $request->type == 'register'){
                            // Notification for welcome

                            $title = "Welcome to Meet Now";
                            $message = "Welcome to Meet Now"; 
                            Helper::send_notification('single', 0, $user->id, $title, 'message', $message, []);
                        }
                    } 
                    return $this->success($data,'OTP verified successfully');
                }
                return $this->error('OTP is wrong','OTP is wrong');
            } 
            $can_not_find = "Sorry we can not find data with this credentials";
            return $this->error($can_not_find,$can_not_find);

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // RETRIVE DATA WHICH ARE NEEDED FOR REGISTRATION FORM

    public function getRegistrationFormData(){
        try{
            $data               = [];
            $data['hobby']      = Hobby::all();
            $data['gender']     = Gender::all();
            return $this->success($data,'Registration form data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // USER REGISTRATION

    public function register(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|unique:users,email|max:255',
                'phone_no'   => 'required|string|unique:users,phone_no|max:20',
                'location'   => 'required|string|max:255',
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
                'gender'     => 'required',
                'interested_gender'     => 'required',
                'media'      => 'required',
                'birth_date' => 'required',
                'media'      => 'required|array|min:3',
                'media.*'    => 'required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:100000',
                'thumbnail_image' => 'sometimes|file|mimes:jpeg,png,jpg',
                'hobbies'    => 'required',
                // 'hobbies' => [
                //     'required',
                //     function ($attribute, $value, $fail) {
                //         $numCommas = substr_count($value, ',');
                //         if ($numCommas > 2) {
                //             $fail('You can select max 3 '.$attribute);
                //         }
                //     },
                // ],
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }   

            $this->sendOtp($request);
            
            $birthdayDate = new DateTime($request->birth_date);
            $currentDate = new DateTime(); 
           
            $input                   = $request->all();
            $input['user_type']      = 'user';
            $input['phone_verified'] = 1;
            $input['age']            = $birthdayDate->diff($currentDate)->y;
            $user_data  = User::create($input);

            if(isset($user_data->id)){

                $folderPath = public_path().'/user_profile';
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }

                if ($request->hasFile('media')) {
                    $medias = $request->file('media');
                  
                    foreach ($medias as $media) {
                        $extension  = $media->getClientOriginalExtension();
                        $filename = 'User_'.$user_data->id.'_'.random_int(10000, 99999). '.' . $extension;
                        $media->move(public_path('user_profile'), $filename);

                        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                            $user_photo_data['type'] = 'image';
                        } elseif ($extension == 'mp4' || $extension == 'avi' || $extension == 'mov') {
                            $user_photo_data['type'] = 'video';
                        } 
                        $user_photo_data['user_id'] = $user_data->id;
                        $user_photo_data['name'] = $filename;
                        UserPhoto::create($user_photo_data);
                    }
                }

                if ($request->hasFile('thumbnail_image')) {
                    $thumbnail_image = $request->file('thumbnail_image');
                    $extension  = $thumbnail_image->getClientOriginalExtension();
                    $filename = 'User_'.$user_data->id.'_'.random_int(10000, 99999). '.' . $extension;
                    $thumbnail_image->move(public_path('user_profile'), $filename);

                    if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                        $user_photo_data['type'] = 'thumbnail_image';
                    } 
                    $user_photo_data['user_id'] = $user_data->id;
                    $user_photo_data['name'] = $filename;
                    UserPhoto::create($user_photo_data);
                } 
                $temp         = Temp::where('key',$request->email)->first();
                if($temp != null){
                    $user_data['otp'] = (int)$temp->value; 
                }
                return $this->success($user_data,'You are successfully registered');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            if(isset($user_data->id)){
                $user_old_photo_name = UserPhoto::where('user_id',$user_data->id)->pluck('name')->toArray();

                $deletedFiles = [];
                if(!empty($user_old_photo_name)){
                    foreach ($user_old_photo_name as $name) {
                        $path = public_path('user_profile/' . $name);
                        if (File::exists($path)) {
                            if (!is_writable($path)) {
                                chmod($path, 0777);
                            }
                            File::delete($path);
                            $deletedFiles[] = $path;
                        }
                    };
                }
                UserPhoto::where('user_id',$user_data->id)->delete();
                User::where('id',$user_data->id)->delete();
            }
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CHECK EMAIL EXISTS OR NOT DURING REGISTRATION AND EMAIL CHANGE FROM MODAL

    public function emailExist(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
                
            $key  = $request->email;
            
            $data['is_email_exist'] = 0;
            if (User::where('email', '=', $key)->count() > 0) {
                $data['is_email_exist'] = 1;
            }
            return $this->success($data,'Email exists check');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
