<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(){
        $data['total_user_count'] = User::where('user_type','user')->count();
        $data['today_user_count'] = User::where('user_type','user')->whereDate('created_at',date('Y-m-d'))->count();
        return view('admin.dashboard',$data);
    }
}
