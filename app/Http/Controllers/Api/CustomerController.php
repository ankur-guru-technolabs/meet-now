<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Api\AuthController;
use App\Models\Bodytype;
use App\Models\Chat;
use App\Models\Education;
use App\Models\Exercise;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\Religion;
use App\Models\User;
use App\Models\UserLikes;
use App\Models\UserPhoto;
use App\Models\UserView;
use App\Models\UserReport;
use App\Models\ContactSupport;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Lib\RtcTokenBuilder;
use DateTime;
use Exception;
use Helper; 
use Validator;
use DB;

class CustomerController extends BaseController
{
    // GET LOGGED IN USER PROFILE

    public function getProfile(Request $request){
        try{ 
            $id = isset($request->id) ? $request->id : Auth::id();
            $data['user']   =  User::with('media','activeSubscription')->find($id);

            $data['user']->media->map(function ($photo) {
                $photo->append('profile_photo');
            });
            $profile_photo_media = $data['user']->media->firstWhere('type', 'profile_image');
            $data['user']->compress_profile_photo   = $profile_photo_media->compress_photo ?? null;
            $hobbies_id                     = $data['user']['hobbies'];
            $hobbies_array                  = explode(",", $hobbies_id); 
            $data['user']['hobbies_new']    = array_map('intval', $hobbies_array);
            $hobbyNames                     = Hobby::whereRaw("FIND_IN_SET(id, '$hobbies_id') > 0")->pluck('name');
            $data['user']['hobbies_name']   = implode(", ", $hobbyNames->toArray());
            $data['user']['gender_new']                = Gender::where('id',$data['user']['gender'])->pluck('gender')->first();
            $data['user']['interested_gender_new']     = Gender::where('id',$data['user']['interested_gender'])->pluck('gender')->first();
            $data['user']['body_type_new']             = Bodytype::where('id',$data['user']['body_type'])->pluck('name')->first();
            $data['user']['education_new']             = Education::where('id',$data['user']['education'])->pluck('name')->first();
            $data['user']['exercise_new']              = Exercise::where('id',$data['user']['exercise'])->pluck('name')->first();
            $data['user']['religion_new']              = Religion::where('id',$data['user']['religion'])->pluck('name')->first();

            if(!isset($request->id)){
                $user_id = Auth::id();
                $today_date = date('Y-m-d H:i:s');

                $is_purchased           = UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->first();
                if($is_purchased != null){
                    $data['user']['plan_data'] = Subscription::where('id',$is_purchased->subscription_id)->first();
                }

                $free_subscription      = Subscription::where('plan_type','free')->pluck('search_filters')->first();
                $paid_subscription      = Subscription::where('plan_type','paid')->pluck('search_filters')->first();
                $data['user']['free']           = explode(',',$free_subscription);
                $data['user']['paid']           = explode(',',$paid_subscription);
            }

            if($id != Auth::id()){
                
                // Check user is already liked and then after view profile ? in that scnario no data will inserted

                $user_likes = UserLikes::where('like_from',Auth::id())->where('like_to',$id)->first();
                $user_view = UserView::where('view_from',Auth::id())->where('view_to',$id)->first();
                if(empty($user_likes) && empty($user_view)){
                    UserView::create(['view_from'=>Auth::id(),'view_to'=> $id]);

                    // Notification for profile view

                    $title = "Your profile has been viewed by ".Auth::user()->name;
                    $message = "Your profile has been viewed by ".Auth::user()->name; 
                    
                    if($data['user']->activeSubscription == null){
                        $title = "Your profile has been viewed by someone.";
                        $message = "Your profile has been viewed by someone."; 
                    }
                    Helper::send_notification('single', Auth::id(), $id, $title, 'user_view', $message, []);
                };
            }
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
                'media.*'    => 'sometimes|required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:100000',
                'profile_image'   => 'sometimes|file|mimes:jpeg,png,jpg',
                'thumbnail_image' => 'sometimes|file|mimes:jpeg,png,jpg',
                'hobbies'    => 'required',
                'body_type'  => 'required',
                'education'  => 'required',
                'exercise'   => 'required',
                'religion'   => 'required',
                'about'      => 'required',
                'distance_in'=> 'required',
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

            $user_data = User::where('id',Auth::id())->first();

            $otp = 0;
            if($user_data){
                if($user_data->email != $request->email){
                    // $user_data->email_verified = 0;
                    // $user_data->otp_verified = 0;
                    // $user_data->save();
                    $response = (new AuthController)->sendOtp($request);
                    $data11 = json_decode($response->getContent(), true);  
                    if ($data11 && isset($data11['data']['otp'])) {
                        $otp = (int)$data11['data']['otp'];  
                    } 
                }
                if($user_data->birth_date != $request->birth_date){
                    $birthdayDate = new DateTime($request->birth_date);
                    $currentDate  = new DateTime(); 
                    $request->merge(['age' => $birthdayDate->diff($currentDate)->y]);
                }

                $user_data->update($request->except(['phone_no','email']));

                $folderPath = public_path().'/user_profile';

                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }

                $mediaFiles = $request->file('media');
                $thumbnailImage = $request->file('thumbnail_image');
                $profileImage = $request->file('profile_image');
                $user_photo_data = [];
                
                if (isset($request->image)) {

                    $userPhotos = UserPhoto::whereIn('id', $request->image)->where('user_id',$request->user_id)->where('type','!=','thumbnail_image');
                    $user_old_photo_name = $userPhotos->pluck('name')->toArray();
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
                    $userPhotos->delete();
                }

                if (!empty($mediaFiles)) {
                    $user_photo_data = $this->uploadMediaFiles($mediaFiles, $user_data->id);
                }

                if (isset($request->is_thumbnail_change) && $request->is_thumbnail_change == 1) {
                    $this->deleteUserPhotos(null, $request->user_id, 'thumbnail_image');
                    if(!empty($thumbnailImage)){
                        // $user_photo_data[] = $this->uploadImageFile($thumbnailImage, $user_data->id, 'thumbnail_image');
                        $user_photo_data = array_merge($user_photo_data, $this->uploadImageFile($thumbnailImage, $user_data->id, 'thumbnail_image'));
                    } 
                }

                if (!empty($profileImage)) {
                    $this->deleteUserPhotos(null, $request->user_id, 'profile_image');
                    $this->deleteUserPhotos(null, $request->user_id, 'compress_profile_image');
                    // $user_photo_data[] = $this->uploadImageFile($profileImage, $user_data->id, 'profile_image');
                    $user_photo_data = array_merge($user_photo_data,$this->uploadImageFile($profileImage, $user_data->id, 'profile_image'));
                }
                UserPhoto::insert($user_photo_data);

                $user_data->new_email = null;
                if($user_data->email != $request->email){
                    $user_data->new_email = $request->email;
                    if($otp > 0){
                        $user_data->otp = $otp;
                    }
                }
                return $this->success($user_data,'Your profile successfully updated');
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
            $data['user']           = User::select('id','hobbies','interested_gender','body_type','education','religion')->find(Auth::id());
            $hobbies_id             = $data['user']['hobbies']; 

            $hobbies_array          = explode(",", $hobbies_id); 
            $data['hobbies_new']    = array_map('intval', $hobbies_array);

            $data['body_type']      = Bodytype::select('id','name')->get();
            $data['education']      = Education::select('id','name')->get();
            $data['gender']         = Gender::select('id','gender')->get();
            $data['hobby']          = Hobby::select('id','name')->get();
            $data['religion']       = Religion::select('id','name')->get();
            $data['min_age']        = (int)env('MIN_AGE', 18);
            $data['max_age']        = (int)env('MAX_AGE', 30);
            $data['min_distance']   = (int)env('MIN_DISTANCE', 1);
            $data['max_distance']   = (int)env('MAX_DISTANCE', 1);
            $data['default_distance']= (int)env('DEFAULT_DISTANCE', 1);

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
            
            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');
            $is_purchased = UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->first();
            if($is_purchased === null){
                $today_like_count = UserLikes::where('like_from',$user_id)->whereDate('created_at', date('Y-m-d'))->count();
                $free_plan_likes = Subscription::where('plan_type','free')->first();
                if($today_like_count > $free_plan_likes->like_per_day){
                    return $this->error('You have already reached at maximum like limit','You have already reached at maximum like limit');
                };
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
                $maxId = UserLikes::where('match_id', '>', 0)->max('match_id');
             
                $input['match_id']      = ($maxId > 10000 || $maxId == 10000) ? $maxId + 1 : 10000;
                $input['match_status']  = 1;
                $input['matched_at']    = now();
                
                UserLikes::where('like_from',$input['like_to'])->where('like_to',$input['like_from'])->where('status',$input['status'])->update(
                    ['match_id' => $input['match_id'],'match_status' => $input['match_status'],'matched_at' => $input['matched_at']]);
                
                
                $receiver_data = User::where('id',$input['like_to'])->first();

                $sender_image =  asset('images/meet-now.png');
                $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();
    
                if(!empty($login_user_image_data)){
                    $sender_image = $login_user_image_data->profile_photo;
                }
                
                $receiver_image =  asset('images/meet-now.png');
                $login_user_image_data = UserPhoto::where('user_id',$input['like_to'])->where('type','profile_image')->first();
    
                if(!empty($login_user_image_data)){
                    $receiver_image = $login_user_image_data->profile_photo;
                }
                
                // Notification for match profile both side
                
                $title = "Congrats! You have a match with ".Auth::user()->name;
                $message = "Congrats! You have a match with ".Auth::user()->name; 
                $data_for_receiver = array('match_id' => $input['match_id'],'sender_id'=> $input['like_to'],'sender_name' => $receiver_data['name'],'sender_image'=> $receiver_image,'receiver_id'=> Auth::id(),'receiver_name' => Auth::user()->name,'receiver_image'=> $sender_image);
                Helper::send_notification('single', Auth::id(), $input['like_to'], $title, 'match', $message, $data_for_receiver);
                
                // Notification for match profile both side
                
                $title = "Congrats! You have a match with ". $receiver_data['name'];
                $message = "Congrats! You have a match with ". $receiver_data['name']; 
                $data_for_sender = array('match_id' => $input['match_id'],'sender_id'=> Auth::id(),'sender_name' => Auth::user()->name,'sender_image'=> $sender_image,'receiver_id'=> $input['like_to'],'receiver_name' => $receiver_data['name'],'receiver_image'=> $receiver_image);
                Helper::send_notification('single', $input['like_to'], Auth::id(), $title, 'match', $message, $data_for_sender);
            }

            if(!$same_request){
                // Check logged in user viewd opposite user profile and now liking that user profile then delete

                if($input['status'] == 1 || $input['status'] == 0){
                    UserView::where('view_from',Auth::id())->where('view_to',$input['like_to'])->delete();
                }
                
                // Check logged in user's profile viewd by opposite user profile and now logged in user liking or disliking that user profile then delete

                UserView::where('view_from',$input['like_to'])->where('view_to',Auth::id())->delete();

                UserLikes::create($input);

                // Notification for profile like

                if($input['status'] == 1){
                    $title = "Your profile is liked by ".Auth::user()->name;
                    $message = "Your profile is liked by ".Auth::user()->name; 
                    $another_user_data   =  User::with('media','activeSubscription')->find($input['like_to']);
                    if($another_user_data->activeSubscription == null){
                        $title = "Your profile is liked by someone.";
                        $message = "Your profile is liked by someone."; 
                    }
                    Helper::send_notification('single', Auth::id(), $input['like_to'], $title, 'like', $message, []);
                }
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
                // 'hobbies' => 'required',
                'min_age'     => 'required',
                'max_age'     => 'required|gte:min_age',
                'min_distance'=> 'required',
                'max_distance'=> 'required|gte:min_distance',
                'latitude'    => 'required',
                'longitude'   => 'required',
                'distance_in' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            $auth_lat1 = deg2rad($request->latitude);
            $auth_lon1 = deg2rad($request->longitude);

            $earthRadius = 3959; 

            $query  = User::where('users.id', '!=', Auth::id())
                                ->where('user_type', 'user')
                                ->where('users.status', 1)
                                ->where('is_hide_profile', 0)
                                ->where('gender', $request->interested_gender)
                                ->where('interested_gender', Auth::user()->gender)
                                ->whereBetween(\DB::raw('CAST(age AS SIGNED)'), [$request->min_age, $request->max_age]);
                                
                                if($request->has('hobbies')) {
                                    $query->where(function($query) use ($request) {
                                        if(isset($request->hobbies)) {
                                            $hobby_ids = explode(',', $request->hobbies);
                                            foreach($hobby_ids as $id) {
                                                $query->orWhereRaw("FIND_IN_SET($id, hobbies)");
                                            }
                                        }
                                    });
                                }
                                if($request->has('body_type')) {
                                    $query->whereIn('body_type', explode(',', $request->body_type));
                                }
                                if($request->has('education')) {
                                    $query->whereIn('education', explode(',', $request->education));
                                }
                                if($request->has('religion')) {
                                    $query->whereIn('religion', explode(',', $request->religion));
                                }
                                $query->leftJoin('user_likes as ul1', function ($join) {
                                    $join->on('users.id', '=', 'ul1.like_from')
                                         ->where('ul1.like_to', '=', Auth::id());
                                })
                                ->leftJoin('user_likes as ul2', function ($join) {
                                    $join->on('users.id', '=', 'ul2.like_to')
                                         ->where('ul2.like_from', '=', Auth::id());
                                })
                                ->whereNull('ul1.id')
                                ->whereNull('ul2.id')
                                ->where('users.updated_at', '>=', now()->subMinutes(5));

            $user_list  =   $query->select('users.id', 'name', 'location', 'age','live_latitude','live_longitude')->get();
            
            $data['user_list']  =   $user_list->map(function ($user) use ($request, $auth_lat1, $auth_lon1, $earthRadius) {
                                    if(!empty($user->live_latitude) && !empty($user->live_longitude)){
                                        $profile_photo_media = $user->media->firstWhere('type', 'profile_image');
                                        $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                        $user->compress_profile_photo = $profile_photo_media->compress_photo ?? null;
                                        unset($user->media);
                                        $lat2 = deg2rad($user->live_latitude);
                                        $lon2 = deg2rad($user->live_longitude);
                                        $dLat = $lat2 - $auth_lat1;
                                        $dLon = $lon2 - $auth_lon1;
                                        $a = sin($dLat/2) * sin($dLat/2) + cos($auth_lat1) * cos($lat2) * sin($dLon/2) * sin($dLon/2);
                                        $c = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
                                        if($request->distance_in == 0){
                                            $distance = round($c * 1760,2);  
                                        }
                                        if($request->distance_in == 1){
                                            $distance = round($c,2);  
                                        }
                                        $user->distance = $distance;
                                        $user->distance_in = $request->distance_in;
                                        if($distance >= $request->min_distance && $distance <= $request->max_distance ){
                                            return $user;
                                        }
                                    }
                                })->filter();

            if (empty($data['user_list'][0])) {
                $data['user_list'] = [];
            }
            return $this->success($data,'Discovery list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CARD DISCOVER PROFILE

    public function cardDiscoverProfile(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'interested_gender' => 'required',
                // 'hobbies' => 'required',
                'min_age'     => 'required',
                'max_age'     => 'required|gte:min_age',
                'location'    => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            // $auth_lat1 = deg2rad($request->latitude);
            // $auth_lon1 = deg2rad($request->longitude);

            // $earthRadius = 3959; 

            $query  = User::where('users.id', '!=', Auth::id())
                                ->where('user_type', 'user')
                                ->where('users.status', 1)
                                ->where('is_hide_profile', 0)
                                ->where('gender', $request->interested_gender)
                                ->where('interested_gender', Auth::user()->gender)
                                ->whereBetween(\DB::raw('CAST(age AS SIGNED)'), [$request->min_age, $request->max_age]);

                                if($request->has('location')){                                        
                                    $query->where('location', $request->location);
                                } 
                                
                                if($request->has('hobbies')) {
                                    $query->where(function($query) use ($request) {
                                        if(isset($request->hobbies)) {
                                            $hobby_ids = explode(',', $request->hobbies);
                                            foreach($hobby_ids as $id) {
                                                $query->orWhereRaw("FIND_IN_SET($id, hobbies)");
                                            }
                                        }
                                    });
                                }
                                if($request->has('body_type')) {
                                    $query->whereIn('body_type', explode(',', $request->body_type));
                                }
                                if($request->has('education')) {
                                    $query->whereIn('education', explode(',', $request->education));
                                }
                                if($request->has('religion')) {
                                    $query->whereIn('religion', explode(',', $request->religion));
                                }
                                $query->leftJoin('user_likes as ul1', function ($join) {
                                    $join->on('users.id', '=', 'ul1.like_from')
                                         ->where('ul1.like_to', '=', Auth::id());
                                })
                                ->leftJoin('user_likes as ul2', function ($join) {
                                    $join->on('users.id', '=', 'ul2.like_to')
                                         ->where('ul2.like_from', '=', Auth::id());
                                })
                                ->whereNull('ul1.id')
                                ->whereNull('ul2.id');
                                // ->where('users.updated_at', '>=', now()->subMinutes(5));

            $user_list  =   $query->select('users.id', 'name', 'location', 'age','latitude','longitude')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['user_list']  =   $user_list->map(function ($user) use ($request) {
            // $data['user_list']  =   $user_list->map(function ($user) use ($request, $auth_lat1, $auth_lon1, $earthRadius) {
                                        // if(!empty($user->latitude) && !empty($user->longitude)){
                                            $profile_photo_media = $user->media->firstWhere('type', 'profile_image');
                                            $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                            $user->compress_profile_photo = $profile_photo_media->compress_photo ?? null;
                                            unset($user->media);
                                            return $user;
                                            // $lat2 = deg2rad($user->latitude);
                                            // $lon2 = deg2rad($user->longitude);
                                            // $dLat = $lat2 - $auth_lat1;
                                            // $dLon = $lon2 - $auth_lon1;
                                            // $a = sin($dLat/2) * sin($dLat/2) + cos($auth_lat1) * cos($lat2) * sin($dLon/2) * sin($dLon/2);
                                            // $c = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
                                            // if($request->distance_in == 0){
                                            //     $distance = round($c * 1760,2);  
                                            // }
                                            // if($request->distance_in == 1){
                                            //     $distance = round($c,2);  
                                            // }
                                            // $user->distance = $distance;
                                            // $user->distance_in = $request->distance_in;
                                            // if($distance >= $request->min_distance && $distance <= $request->max_distance ){
                                            //     return $user;
                                            // }
                                        // }
                                    })->filter();

            $data['current_page'] = $user_list->currentPage();
            $data['per_page']     = $user_list->perPage();
            $data['total']        = $user_list->total();
            $data['last_page']    = $user_list->lastPage();                        
            // if (empty($data['user_list'][0])) {
            //     $data['user_list'] = [];
            // }
            return $this->success($data,'Discovery list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
  
    // MATCHED USER LISTING
    
    public function matchedUserList(Request $request){
        try{
            $matched_user_listing = UserLikes::where('user_likes.like_to',Auth::id())
                                        ->where('user_likes.status',1)
                                        ->where('user_likes.match_status',1)
                                        ->where('user_likes.match_id','>',0)
                                        ->leftJoin('chats as c', function ($join) {
                                            $join->on('user_likes.match_id', '=', 'c.match_id');
                                        }) 
                                        ->whereNull('c.id') 
                                        ->select('user_likes.id', 'user_likes.like_from','user_likes.like_to','user_likes.match_id')
                                        ->get();

            $today_date         = date('Y-m-d H:i:s');
            $data['matched_user_listing'] = $matched_user_listing->map(function ($user,$today_date){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->media->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                $user->active_plan = UserSubscription::where('user_id',$user->users->first()->id)->where('expire_date','>',$today_date)->count();
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();
                                        
          
            return $this->success($data,'Matched user listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET CHAT LIST
    
    public function chatList(Request $request){
        try{
            $chat_list          =   Chat::where(function ($query) {
                                        $query->where('receiver_id', Auth::id())
                                            ->orWhere('sender_id', Auth::id());
                                    })
                                    ->join(DB::raw('(SELECT MAX(id) AS latest_chat_id FROM chats GROUP BY match_id) AS latest_chats'), 'chats.id', '=', 'latest_chats.latest_chat_id')
                                    ->select('chats.id', 'chats.match_id','chats.sender_id','chats.receiver_id','chats.read_status','chats.type')
                                    ->selectRaw('MAX(chats.message) as last_message')
                                    ->selectRaw('(SELECT COUNT(*) FROM chats AS sub_chats WHERE sub_chats.match_id = chats.match_id AND sub_chats.read_status = 0 AND sub_chats.receiver_id = '.Auth::id().') as unread_message_count')
                                    ->leftJoin('user_likes as ul', function ($join) {
                                        $join->on('chats.match_id', '=', 'ul.match_id');
                                    })
                                    ->where('ul.match_status',1) 
                                    ->groupBy('chats.match_id')
                                    ->orderBy('chats.created_at', 'desc')
                                    ->orderBy('chats.id', 'desc')
                                    ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
                                    
            $today_date         = date('Y-m-d H:i:s');
            $data['chat_list']  =   $chat_list->map(function ($user,$today_date){
                                            if($user->sender_id == Auth::id() && $user->userReceiver->isNotEmpty()){
                                                $profile_photo_media = $user->userReceiver->first()->media->firstWhere('type', 'profile_image'); 
                                                $user->user_id = $user->userReceiver->first()->id;
                                                $user->name = $user->userReceiver->first()->name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                $user->unread_message_count = (int)$user->unread_message_count;
                                                $user->last_message = $user->last_message;
                                                $user->active_plan = UserSubscription::where('user_id',$user->userReceiver->first()->id)->where('expire_date','>',$today_date)->count();
                                                unset($user->userReceiver);
                                            }
                                            if($user->sender_id != Auth::id() && $user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->media->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                $user->unread_message_count = (int)$user->unread_message_count;
                                                $user->last_message = $user->last_message;
                                                $user->active_plan = UserSubscription::where('user_id',$user->users->first()->id)->where('expire_date','>',$today_date)->count();
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();
                                        
            $data['current_page'] = $chat_list->currentPage();
            $data['per_page']     = $chat_list->perPage();
            $data['total']        = $chat_list->total();
            $data['last_page']    = $chat_list->lastPage();
            return $this->success($data,'Chat list');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // CHANGE MESSAGE READ STATUS 
    
    public function changeReadStatus(Request $request){
        try{
            $chat_read_status   =   Chat::where('receiver_id',Auth::id())
                                    ->where('match_id',$request->match_id)
                                    ->update(['read_status' => 1]);
          
            return $this->success([],'Chat read successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // SEND MESSAGE 

    public function sendMessage(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'match_id' => 'required',
                'receiver_id' => 'required',
                'message' => 'required',
                'type' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $chats = new Chat();
            $chats->match_id    = $request->match_id;
            $chats->sender_id   = Auth::id();
            $chats->receiver_id = $request->receiver_id;
            $chats->message     = $request->message;
            $chats->type        = $request->type;
            $chats->save();

            $sender_image =  asset('images/meet-now.png');
            $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

            if(!empty($login_user_image_data)){
                $sender_image = $login_user_image_data->profile_photo;
            }
            // Notification for message send
            $custom = [
                'sender_id'     =>  Auth::id(),
                'match_id'      =>  $request->match_id,
                'sender_name'   =>  Auth::user()->name,
                'sender_image'  =>  $sender_image,
                'image'         =>  $sender_image,
            ]; 
            
            $title = Auth::user()->name." sent you a message";
            $message = Auth::user()->name." sent you a message"; 
            Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'message', $message, $custom);

            return $this->success([],'Message send successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UNMATCH 

    public function unmatch(Request $request){
        try{

            $validateData = Validator::make($request->all(), [
                'match_id' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            UserLikes::where('user_likes.match_id',$request->match_id)->update(['user_likes.match_status' => 0]);

            $user_data =  UserLikes::where('user_likes.match_id',$request->match_id)->first();

            $notification_receiver_id = 0;
            if($user_data->like_from != Auth::id()){
                $notification_receiver_id = $user_data->like_from;
            }

            if($user_data->like_to != Auth::id()){
                $notification_receiver_id = $user_data->like_to;
            } 
            
            // Notification for unmatch profile both side

            $title = "You have unmatched with ".Auth::user()->name;
            $message = "You have unmatched with ".Auth::user()->name; 
            Helper::send_notification('single', Auth::id(), $notification_receiver_id, $title, 'unmatch', $message, []);

            return $this->success([],'Unmatch done successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
  
    // REPORT 

    public function report(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'match_id' => 'required',
                'message' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            UserLikes::where('user_likes.match_id',$request->match_id)->update(['user_likes.match_status' => 0]);

            $user_report = new UserReport();
            $user_report->match_id          = $request->match_id;
            $user_report->reporter_id       = Auth::id();
            $user_report->reported_user_id  = $request->reported_user_id;
            $user_report->message           = $request->message;
            $user_report->save();

            // Notification for report

            $title = Auth::user()->name ." reported your profile";
            $message = Auth::user()->name ." reported your profile"; 
            Helper::send_notification('single', Auth::id(), $request->reported_user_id, $title, 'report', $message, []);

            return $this->success([],'Report done successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // CONTACTSUPPORT 

    public function contactSupport(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'description' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
           
            $support                = new ContactSupport();
            $support->name          = $request->name;
            $support->email         = $request->email;
            $support->description   = $request->description;
            $support->save();

            return $this->success([],'Request added successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // WHO LIKES ME LISTING

    public function whoLikesMe(Request $request){
        try{
            $user_likes_listing = UserLikes::where('user_likes.like_to',Auth::id())
                                        ->where('user_likes.status',1)
                                        ->where('user_likes.match_status',2)
                                        ->select('user_likes.id', 'user_likes.like_from','user_likes.like_to')
                                        ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['user_listing'] = $user_likes_listing->map(function ($user){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->media->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->name;
                                                $user->age = $user->users->first()->age;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();
                                        
            $data['current_page'] = $user_likes_listing->currentPage();
            $data['per_page']     = $user_likes_listing->perPage();
            $data['total']        = $user_likes_listing->total();
            $data['last_page']    = $user_likes_listing->lastPage();
            return $this->success($data,'Who likes me listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // STATIC PAGE DATA

    public function staticPage(Request $request){
        try{
            $data['static_page_data']  = Setting::all();
            return $this->success($data,'Static page data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // WHO VIEWED ME LISTING

    public function whoViewedMe(Request $request){
        try{
            $user_view_listing = UserView::where('user_views.view_to',Auth::id())
                                        ->select('user_views.id', 'user_views.view_from','user_views.view_to')
                                        ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['user_listing'] = $user_view_listing->map(function ($user){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->media->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->name;
                                                $user->age = $user->users->first()->age;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();
            
            $data['current_page'] = $user_view_listing->currentPage();
            $data['per_page']     = $user_view_listing->perPage();
            $data['total']        = $user_view_listing->total();
            $data['last_page']    = $user_view_listing->lastPage();

            return $this->success($data,'Who viewd me listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }


    // USER LIVE LOCATION UPDATE
    
    public function updateLocation(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'latitude'  => 'required',
                'longitude' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            if (Auth::user()) {
                $user_id   = Auth::user()->id;
                $user_data = User::where('id',$user_id)->update(['live_latitude' =>  $request->latitude, 'live_longitude' => $request->longitude]);
                return $this->success([],'Location updated successfullly');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // VIDEO CALL

    public function singleVideoCall(Request $request){
        try{
            $validateData = Validator::make($request->all(),[
                'receiver_id'  => 'required|int',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');
            $is_purchased = UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->first();
            if($is_purchased === null){
                return $this->error('Please purchase subscription for video call','Please purchase subscription for video call');
            }

            if (Auth::user()) { 
                $appID =  env("AGORA_APP_ID", "4f6f13fdda8c4d039249274d1b8ac229");
                $appCertificate = env("AGORA_APP_CERTIFICATE", "a05000ab3f024995b468bbec55fbb7b4");

                $channelName = $this->generateRandomChannel(8);
                $userId = $this->generateRandomUid();
                $role = RtcTokenBuilder::RolePublisher;

                $expireTimeInSeconds = 3600;
                $currentTimestamp = now()->getTimestamp();
                $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

                $rtcToken1 = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $userId, $role, $privilegeExpiredTs);

                $sender_image = asset('images/meet-now.png');
                $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

                if(!empty($login_user_image_data)){
                    $sender_image = $login_user_image_data->profile_photo;
                }

                $userIdReceiver = $this->generateRandomUid();
                $roleReceiver = RtcTokenBuilder::RoleSubscriber;
                $rtcTokenReceiver = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $userIdReceiver, $roleReceiver, $privilegeExpiredTs);

                $data = [
                    'sender_id'     =>  Auth::id(),
                    'receiver_id'   =>  $request->receiver_id,
                    'receiver_u_id' =>  $userId,
                    'channel_name'  =>  $channelName,
                    'receiver_token'=>  $rtcToken1,
                    'sender_name'   =>  Auth::user()->name,
                    'sender_image'  =>  $sender_image,
                    'image'         =>  $sender_image,
                    'userIdReceiver'  =>  $userIdReceiver,
                    'roleReceiver'  =>  $roleReceiver,
                    'rtcTokenReceiver'  =>  $rtcTokenReceiver,
                ];   

                // Notification for video call

                $title = "You have a video call request from ".Auth::user()->name;
                $message = "You have a video call request from ".Auth::user()->name;  
                Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'video_call', $message, $data);


                return $this->success($data,'Video call done');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    public function generateRandomChannel($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateRandomUid($length = 9) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function declineVideoCall(Request $request)
    {
        try
        {
            $validateData = Validator::make($request->all(),[
                'receiver_id'  => 'required',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user = User::where('id',Auth::id())->first();

            $receiver_image =  asset('images/meet-now.png');
            $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

            if(!empty($login_user_image_data)){
                $receiver_image = $login_user_image_data->profile_photo;
            }
            
            $data = [
                'sender_id'     =>  Auth::id(), 
                'receiver_id'   =>  $request->receiver_id, 
                'receiver_image'  =>  $receiver_image,  
                'image'         =>  $receiver_image,         
            ];      

            $title = "Video call is decline by ".$user->name;
            $message = "Video call is decline by ".$user->name; 
 
            Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'decline_call', $message, $data);
            return $this->success([],'Video call declined');
        }
        catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur!');
        }
    }

    // FCM TOKEN SET

    public function updateFcmToken(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'fcm_token' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            User::where('id',Auth::id())->update(['fcm_token' => $request->fcm_token]);
           
            return $this->success([],'Token updated successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // NOTIFICATION LIST

    public function notificationList(Request $request){
        try{
            $notification_id  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->take(30)->pluck('id')->toArray();
            Notification::whereNotIn('id', $notification_id)->where('receiver_id',Auth::id())->delete();
            
            $notification_data  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->take(30)->get();
            $data['notification_data'] = $notification_data->map(function ($notification){
                $date = date('d/m/Y', strtotime($notification->created_at));

                if($date == date('d/m/Y')) {
                    $notification->date = 'Today';
                }else if($date == date('d/m/Y', strtotime('-1 day'))) {
                    $notification->date = 'Yesterday';
                }else{
                    $notification->date = date('d M', strtotime($notification->created_at));
                } 

                $profile_photo_media = !empty($notification->notificationSender->first()) ? $notification->notificationSender->first()->media->firstWhere('type', 'profile_image') : null;
                $notification->name = !empty($notification->notificationSender->first()) ? $notification->notificationSender->first()->name : 'Admin';
                $notification->profile_photo = $profile_photo_media->profile_photo ?? null;
                unset($notification->notificationSender);
                
                return $notification;
            })->values();
            return $this->success($data,'Notification data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // NOTIFICATION READ

     public function notificationRead(){
        try{
            Notification::where('receiver_id',Auth::id())->update(['status'=>1]);
            return $this->success([],'Notification read successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
        
    // NOTIFICATION SETTING

    public function notificationSetting(){
        try{
            $user_data = User::where('id',Auth::id())->first();
            if($user_data['is_notification_mute'] == '0'){
                $user_data['is_notification_mute'] = '1';
                $user_data->save();
                return $this->success([],'Notification disable successfully');
            }

            if($user_data['is_notification_mute'] == 1){
                $user_data['is_notification_mute'] = 0;
                $user_data->save();
                return $this->success([],'Notification enable successfully');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GHOST MODE SETTING

    public function ghostModeSetting(){
        try{
            $user_data = User::where('id',Auth::id())->first();
            if($user_data['is_hide_profile'] == 0){
                $user_data['is_hide_profile'] = 1;
                $user_data->save();
                return $this->success([],'Ghost mode enable successfully');
            }

            if($user_data['is_hide_profile'] == 1){
                $user_data['is_hide_profile'] = 0;
                $user_data->save();
                return $this->success([],'Ghost mode disable successfully');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // SUBSCRIPTION LISTING
    
    public function subscriptionList(Request $request){
        try{
            $data['subscription_list'] = Subscription::all();
            return $this->success($data,'Subscription listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // PURCHASE SUVSCRIPTION
    
    public function purchaseSubscription(Request $request){
        try{
            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');
            $is_purchased = UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->first();
            if($is_purchased === null){
                $plan_data = Subscription::where('id',$request->subscription_id)->first();
                $user_subscription                  = new UserSubscription();
                $user_subscription->user_id         =  $user_id; 
                $user_subscription->subscription_id =  $plan_data->id; 
                $user_subscription->expire_date     =  Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
                $user_subscription->title           =  $plan_data->title; 
                $user_subscription->price           =  $plan_data->price; 
                $user_subscription->currency_code   =  $plan_data->currency_code; 
                $user_subscription->month           =  $plan_data->month; 
                $user_subscription->plan_duration   = $plan_data->plan_duration; 
                $user_subscription->plan_type       = $plan_data->plan_type; 
                $user_subscription->save(); 

                // Notification for subscription purchase

                $title = $plan_data->title." purchased successfully";
                $message = $plan_data->title." purchased successfully"; 
                Helper::send_notification('single', 0, Auth::id(), $title, 'subscription_purchase', $message, []);

                return $this->success([],'Subscription purchased successfully');
            }
            return $this->error('You have already purchased plan','You have already purchased plan');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // ACTIVE SUBSCRIPTION LISTING 
    
    public function activeSubscriptionList(Request $request){
        try{
            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');
            $is_purchased = UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->first();
            if($is_purchased != null){
                $data= Subscription::where('id',$is_purchased->subscription_id)->first();
            }else{
                $data= Subscription::where('plan_type','free')->first();
            }
            $today_like_count       = UserLikes::where('like_from',Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->count();
            $data['remaining_likes'] = (int)$data['like_per_day'] - $today_like_count;
            
            return $this->success($data,'Active subscription successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER LOGOUT

    public function logout(){
        try{
            if (Auth::user()) {
                User::where('id',Auth::id())->update(['fcm_token' => null]);
                $user = Auth::user()->token();
                $user->revoke();
                return $this->success([],'You are successfully logout');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    } 
}
