<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\Bodytype;
use App\Models\ContactSupport;
use App\Models\Education;
use App\Models\Exercise;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\Religion;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserReport;
use App\Models\UserSubscription;
use Validator;
use Helper; 
use Auth;

class AdminController extends BaseController
{
    // BODYTYPE

    public function bodyTypeList(){
        $bodyTypes = Bodytype::all();
        return view('admin.bodytype.list',compact('bodyTypes'));
    }
    
    public function bodyTypeStore(Request $request){
        $bodyTypes = new Bodytype;
        $bodyTypes->name = $request->name;
        $bodyTypes->save();
        return redirect()->route('questions.bodytype.list')->with('message','Body type added Successfully'); 
    }
    
    public function bodyTypeUpdate(Request $request){
        $bodyTypes = Bodytype::find($request->id);
        if ($bodyTypes) {
            $bodyTypes->name = $request->name;
            $bodyTypes->save();
        } 
        return redirect()->route('questions.bodytype.list')->with('message','Body type updated Successfully'); 
    }
    
    public function bodyTypeDelete($id){
        $bodyTypes = Bodytype::findOrFail($id);
        $bodyTypes->delete();
        return redirect()->route('questions.bodytype.list')->with('message','Body type deleted Successfully');
    }

    // EDUCATION

    public function educationList(){
        $educations = Education::all();
        return view('admin.education.list',compact('educations'));
    }
    
    public function educationStore(Request $request){
        $educations = new Education;
        $educations->name = $request->name;
        $educations->save();
        return redirect()->route('questions.education.list')->with('message','Education added Successfully'); 
    }

    public function educationUpdate(Request $request){
        $educations = Education::find($request->id);
        if ($educations) {
            $educations->name = $request->name;
            $educations->save();
        }  
        return redirect()->route('questions.education.list')->with('message','Education updated Successfully'); 
    }
    
    public function educationDelete($id){
        $educations = Education::findOrFail($id);
        $educations->delete();
        return redirect()->route('questions.education.list')->with('message','Education deleted Successfully');
    }
    
    // EXERCISE

    public function exerciseList(){
        $exercises = Exercise::all();
        return view('admin.exercise.list',compact('exercises'));
    }
    
    public function exerciseStore(Request $request){
        $exercises = new Exercise;
        $exercises->name = $request->name;
        $exercises->save();
        return redirect()->route('questions.exercise.list')->with('message','Exercise added Successfully'); 
    }

    public function exerciseUpdate(Request $request){
        $exercises = Exercise::find($request->id);
        if ($exercises) {
            $exercises->name = $request->name;
            $exercises->save();
        }  
        return redirect()->route('questions.exercise.list')->with('message','Exercise updated Successfully'); 
    }
    
    public function exerciseDelete($id){
        $exercises = Exercise::findOrFail($id);
        $exercises->delete();
        return redirect()->route('questions.exercise.list')->with('message','Exercise deleted Successfully');
    }

    // GENDER

    public function genderList(){
        $genders = Gender::all();
        return view('admin.gender.list',compact('genders'));
    }
    
    public function genderStore(Request $request){
        $genders = new Gender;
        $genders->gender = $request->gender;
        $genders->save();
        return redirect()->route('questions.gender.list')->with('message','Gender added Successfully'); 
    }

    public function genderUpdate(Request $request){
        $genders = Gender::find($request->id);
        if ($genders) {
            $genders->gender = $request->gender;
            $genders->save();
        }   
        return redirect()->route('questions.gender.list')->with('message','Gender updated Successfully'); 
    }
    
    public function genderDelete($id){
        $genders = Gender::findOrFail($id);
        $genders->delete();
        return redirect()->route('questions.gender.list')->with('message','Gender deleted Successfully');
    }

    // HOBBY

    public function hobbyList(){
        $hobbies = Hobby::all();
        return view('admin.hobby.list',compact('hobbies'));
    }
    
    public function hobbyStore(Request $request){
        $hobbies = new Hobby;
        $hobbies->name = $request->name;
        $hobbies->save();
        return redirect()->route('questions.hobby.list')->with('message','Hobby added Successfully'); 
    }
    
    public function hobbyUpdate(Request $request){
        $hobbies = Hobby::find($request->id);
        if ($hobbies) {
            $hobbies->name = $request->name;
            $hobbies->save();
        }   
        return redirect()->route('questions.hobby.list')->with('message','Hobby updated Successfully'); 
    }
    
    public function hobbyDelete($id){
        $hobbies = Hobby::findOrFail($id);
        $hobbies->delete();
        return redirect()->route('questions.hobby.list')->with('message','Hobby deleted Successfully');
    }

    // FEEDBACK

    public function feedbackList(){
        $feedbacks = ContactSupport::all();
        return view('admin.feedback.list',compact('feedbacks'));
    }
    
    // RELIGION

    public function religionList(){
        $religions = Religion::all();
        return view('admin.religion.list',compact('religions'));
    }
    
    public function religionStore(Request $request){
        $religions = new Religion;
        $religions->name = $request->name;
        $religions->save();
        return redirect()->route('questions.religion.list')->with('message','Religion added Successfully'); 
    }

    public function religionUpdate(Request $request){
        $religions = Religion::find($request->id);
        if ($religions) {
            $religions->name = $request->name;
            $religions->save();
        }   
        return redirect()->route('questions.religion.list')->with('message','Religion updated Successfully'); 
    }
    
    public function religionDelete($id){
        $religions = Religion::findOrFail($id);
        $religions->delete();
        return redirect()->route('questions.religion.list')->with('message','Religion deleted Successfully');
    }

    // SETTING

    public function staticPagesList(){
        $settings = Setting::all();
        return view('admin.setting.list',compact('settings'));
    }
    
    public function pageEdit($id){
        $settings = Setting::where('id',$id)->first();
        return view('admin.setting.edit',compact('settings'));
    }
    
    public function pageUpdate(Request $request){
        
        $validator = Validator::make($request->all(),[
            'id'=>"required",
            'title'=>"required",
            'description'=>"required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $input = $request->all();
        $insert_data['title']       = $input['title'];
        $insert_data['value']       = $input['description'];
        
        Setting::where('id',$request->id)->update($insert_data);
        return redirect()->route('static-pages.list')->with('message','Page updated Successfully'); 
    }
    
    // NOTIFICATION

    public function notificationIndex(){
        return view('admin.notification.index');
    }
    
    public function notificationSend(Request $request){

        $validator = Validator::make($request->all(),[
            'title'=>"required",
            'message'=>"required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }
        
        $title = $request->title;
        $message = $request->message;
        Helper::send_notification_by_admin($title, $message, []);

        return view('admin.notification.index');
    }

    // SUBSCRIPTION

    public function subscriptionOrder(){
        $orders = UserSubscription::with('user','subscriptionOrder')->get();
        return view('admin.subscription.order',compact('orders'));
    }
    
    public function subscriptionList(){
        $subscription = Subscription::all();
        return view('admin.subscription.list',compact('subscription'));
    }
    
    public function subscriptionEdit($id){
        $subscription = Subscription::where('id',$id)->first();
        $subscription['allowed_subscription'] = explode(',',$subscription->search_filters);
        return view('admin.subscription.edit',compact('subscription'));
    }
    
    public function subscriptionUpdate(Request $request){
        $validator = Validator::make($request->all(),[
            'id'=>"required",
            'title'=>"required",
            'description'=>"required",
            'search_filters'=>"required",
            'like_per_day'=>"required",
            'video_call'=>"required",
            'who_like_me'=>"required",
            'who_view_me'=>"required",
            'message_per_match'=>"required",
            'price'=>"required",
            'currency_code'=>"required",
            'month'=>"required",
            'plan_duration'=>"required",
            'google_plan_id' => $request->plan_type === 'free' ? 'nullable' : 'required',
            'apple_plan_id' => $request->plan_type === 'free' ? 'nullable' : 'required',
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $input = $request->all();
        $insert_data['title']             = $input['title'];
        $insert_data['description']       = $input['description'];
        $insert_data['search_filters']    = implode(',',$input['search_filters']);
        $insert_data['like_per_day']      = $input['like_per_day'];
        $insert_data['video_call']        = $input['video_call'];
        $insert_data['who_like_me']       = $input['who_like_me'];
        $insert_data['who_view_me']       = $input['who_view_me'];
        $insert_data['message_per_match'] = $input['message_per_match'];
        $insert_data['price']             = $input['price'];
        $insert_data['currency_code']     = $input['currency_code'];
        $insert_data['month']             = $input['month'];
        $insert_data['plan_duration']     = $input['plan_duration'];
        $insert_data['google_plan_id']    = $input['google_plan_id'];
        $insert_data['apple_plan_id']     = $input['apple_plan_id'];
        
        Subscription::where('id',$request->id)->update($insert_data);
        return redirect()->route('subscription.list')->with('message','Subscription updated Successfully'); 
    }

    // REPORT

    public function reportList(){
        $report = UserReport::with(['reporter:id,name','reportedUser:id,name,status'])->get(); 
        return view('admin.report.list',compact('report'));
    }

    public function userBlock(Request $request){
        try{
            $userId = $request->input('id');
            $user = User::find($userId); 
            if($user){
                $user->status = $request->status;
                $user->fcm_token = null;
                $user->save();

                $tokens = $user->tokens;
        
                foreach ($tokens as $token) {
                    $token->revoke();
                }
                return $this->success([],'User block successfully');
            }
            return $this->error('User not found','User not found');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
