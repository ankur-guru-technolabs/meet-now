<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\API\AuthController;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\User;
use App\Models\UserLikes;
use App\Models\UserPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Exception;
use Helper;
use Validator;

class CustomerController extends BaseController
{
    // GET LOGGED IN USER PROFILE

    public function getProfile(Request $request){
        try{ 
            $data['user']   =  User::with('media')->find($request->id);

            $data['user']->media->map(function ($photo) {
                $photo->append('profile_photo');
            });

            $hobbies_id                     = $data['user']['hobbies'];
            $hobbyNames                     = Hobby::whereRaw("FIND_IN_SET(id, '$hobbies_id') > 0")->pluck('name');
            $data['user']['hobbies_new']    = implode(", ", $hobbyNames->toArray());
            $data['user']['gender_new']                = Gender::where('id',$data['user']['gender'])->pluck('gender')->first();
            $data['user']['interested_gender_new']     = Gender::where('id',$data['user']['interested_gender'])->pluck('gender')->first();
            return $this->success($data,'User profile data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UPDATE USER PROFILE

    public function updateProfile(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'user_id'    => 'required',
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|max:255|unique:users,email,'.$request->user_id,
                // 'phone_no'   => 'required|string|unique:users,phone_no|max:20',
                'location'   => 'required|string|max:255',
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
                'gender'     => 'required',
                'interested_gender'     => 'required',
                'birth_date' => 'required',
                'media'      => 'sometimes|required',
                'media.*'   => 'sometimes|required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:10240',
                'hobbies' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $numCommas = substr_count($value, ',');
                        if ($numCommas > 2) {
                            $fail('You can select max 3 '.$attribute);
                        }
                    },
                ],
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $user_data = User::where('id',Auth::id())->first();

            if($user_data){
                if($user_data->email != $request->email){
                    // $user_data->email_verified = 0;
                    // $user_data->otp_verified = 0;
                    // $user_data->save();
                    (new AuthController)->sendOtp($request);
                }
                if($user_data->birth_date != $request->birth_date){
                    $birthdayDate = new DateTime($request->birth_date);
                    $currentDate  = new DateTime(); 
                    $request->merge(['age' => $birthdayDate->diff($currentDate)->y]);
                }

                $user_data->update($request->except(['phone_no','email']));
                if (isset($request->image) && $request->hasFile('media')) {

                    $user_old_photo_name = UserPhoto::whereIn('id', $request->image)->where('user_id',$request->user_id)->pluck('name')->toArray();
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
                    UserPhoto::whereIn('id', $request->image)->where('user_id',$request->user_id)->delete();

                    $medias = $request->file('media');
                    $folderPath = public_path().'/user_profile';

                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }

                    foreach ($medias as $photo) {
                        $extension  = $photo->getClientOriginalExtension();
                        $filename = 'User_'.$user_data->id.'_'.random_int(10000, 99999). '.' . $extension;
                        $photo->move(public_path('user_profile'), $filename);

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

                $user_data->new_email = null;
                if($user_data->email != $request->email){
                    $user_data->new_email = $request->email;
                }
                return $this->success($user_data,'You profile successfully updated');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    
    // GET FILTER DEFAULT DATA

    public function getFilterData(){
        try{ 
            $data['user']           = User::select('id','hobbies','interested_gender')->find(Auth::id());
            $hobbies_id             = $data['user']['hobbies']; 

            $data['hobbies_new']    = explode(",", $hobbies_id); 
            $data['hobby']          = Hobby::select('id','name')->get();
            $data['gender']         = Gender::select('id','gender')->get();
            $data['min_age']        = env('MIN_AGE', 18);
            $data['max_age']        = env('MAX_AGE', 30);
            $data['min_distance']   = env('MIN_DISTANCE', 1);
            $data['max_distance']   = env('MAX_DISTANCE', 1);

            return $this->success($data,'Filter data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // SWIPE PROFILE

    public function swipeProfile(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'like_to' => 'required',
                'status' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            
            $input              = $request->all();
            $input['like_from'] = Auth::id();
            $input['status']    = (strtolower($input['status']) == 'like') ? 1 : 0;
            $input['match_id']  = 0;

            // Check user already liked or disliked opposite user if yes then not insert else create
            
            $same_request = UserLikes::where('like_from',$input['like_from'])->where('like_to',$input['like_to'])->where('status',$input['status'])->exists();
          
            // Check opposite user is already liked or disliked if yes then set match_id,match_status,matched_at else set to default

            $opposite_request = UserLikes::where('like_from',$input['like_to'])->where('like_to',$input['like_from'])->where('status',$input['status'])->exists();
           
            if($opposite_request && $input['status'] == 1){
                $maxId = UserLikes::where('id', '>', 0)->max('id');
             
                $input['match_id']      = $maxId > 10000 ? $maxId + 1 : 10000;
                $input['match_status']  = 1;
                $input['matched_at']    = now();
                
                UserLikes::where('like_from',$input['like_to'])->where('like_to',$input['like_from'])->where('status',$input['status'])->update(
                    ['match_id' => $input   ['match_id'],'match_status' => $input['match_status'],'matched_at' => $input['matched_at']]);
            }

            if(!$same_request){
                UserLikes::create($input);
            }

            return $this->success([],'Profile liked successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

     // DISCOVER PROFILE

     public function discoverProfile(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'interested_gender' => 'required',
                'hobbies' => 'required',
                'min_age' => 'required',
                'max_age' => 'required|gte:min_age',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            $data['user_list'] = User::where('id', '!=', Auth::id())
                                ->where('user_type', 'user')
                                ->where('gender', $request->interested_gender)
                                ->where('interested_gender', Auth::user()->gender)
                                ->whereBetween('age', [$request->min_age, $request->max_age])
                                ->where(function($query) use ($request) {
                                    if($request->hobbies) {
                                        $hobby_ids = explode(',', $request->hobbies);
                                        foreach($hobby_ids as $id) {
                                            $query->orWhereRaw("FIND_IN_SET($id, hobbies)");
                                        }
                                    }
                                })
                                ->select('id', 'name', 'location', 'age')
                                ->get()
                                ->map(function ($user) {
                                    $user->profile_photo = $user->media->first()->profile_photo;
                                    unset($user->media);
                                    return $user;
                                });
        
            return $this->success($data,'Discovery list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER LOGOUT

    public function logout(){
        try{
            if (Auth::user()) {
                $user = Auth::user()->token();
                $user->revoke();
                return $this->success([],'You are succseefully logout');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
