<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\ContactSupport;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\Setting;
use App\Models\Subscription;
use Validator;
use Helper; 
use Auth;

class AdminController extends BaseController
{
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
        
        Subscription::where('id',$request->id)->update($insert_data);
        return redirect()->route('subscription.list')->with('message','Subscription updated Successfully'); 
    }
}
