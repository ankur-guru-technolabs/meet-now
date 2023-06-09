<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\Gender;
use App\Models\Hobby;

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

}
