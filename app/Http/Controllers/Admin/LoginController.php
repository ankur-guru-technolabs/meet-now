<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ContactSupport;
use App\Models\UserSubscription;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Helper;
use Validator;

class LoginController extends Controller
{
    //
    public function showLoginForm()
    {
        if (auth()->check() && Auth::user()->user_type == 'admin') {
            return redirect('dashboard');
        }
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember_me = $request->has('remember_me');
        if (Auth::attempt($credentials)) {
            $lifetime = $remember_me ? 20160 : 60;
            Session::put('session_start_time', time());
            Session::put('session_lifetime', $lifetime);
            return redirect("dashboard");
        }

        return back()->withErrors([
            'error' => 'The provided credentials do not match our records.',
        ]);
    }
    
    public function subscriptionExpire(Request $request){
        $three_day_after_date = date ('Y-m-d', strtotime ('+3 day'));
        $user_subscripion = UserSubscription::with('user:id,name,email')->whereDate('expire_date',$three_day_after_date)->select('id','user_id')->get();

        foreach($user_subscripion as $users){
            $key          = $users->user->email;
            $name         = $users->user->name;
            $email_data   = [
                'name'                => $name,
                'email'               => $key,
                'subscription_expire' => 'subscription_expire',
                'subject'             => 'Meet now subscription expire',
            ];
            Helper::sendMail('emails.subscription_expire', $email_data, $key, '');
        }
    }
   
    public function messageDelete(Request $request){
        $thirty_day_before = date ('Y-m-d', strtotime ('-30 day'));
        $user_subscripion = Chat::whereDate('updated_at','<=',$thirty_day_before)->where('read_status',1)->get();
    }

    public function privacyPolicy(Request $request)
    {
        $privacy_policy = Setting::where('key','privacy_policy')->first();
        return view('privacy_policy',compact('privacy_policy'));
    }
    
    public function termsCondition(Request $request)
    { 
        $terms_condition = Setting::where('key','terms_condition')->first();
        return view('terms_condition',compact('terms_condition'));
    }
    
    public function contactForm(Request $request)
    { 
        return view('contact_form');
    }
    
    public function contactStore(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'description' => 'required',
        ]);

        
        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }
       
        $support                = new ContactSupport();
        $support->name          = $request->name;
        $support->email         = $request->email;
        $support->description   = $request->description;
        $support->save();

        return redirect()->route('contact')->with('message','Submit Successfully'); 
    }
}
