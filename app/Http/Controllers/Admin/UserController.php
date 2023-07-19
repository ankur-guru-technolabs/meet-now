<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;

class UserController extends BaseController
{
    //

    public function list(){
        $users = User::where('user_type','user')->get();
        return view('admin.user.list',compact('users'));
    }
    
    public function updateStatus(Request $request){
        try{
            $user = User::where('id',$request->id)->first();
            if($user){
                $user->status = $request->status;
                $user->fcm_token = null;
                $user->save();

                $tokens = $user->tokens;
        
                foreach ($tokens as $token) {
                    $token->revoke();
                }
                return $this->success([],'Status change successfully');
            }
            return $this->error('User not found','User not found');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
