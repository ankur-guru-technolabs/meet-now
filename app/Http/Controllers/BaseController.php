<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    //
    public function success($data = [], $message = null, $code = 200)
	{
		return response()->json([
			'status'=> true, 
			'message' => $message, 
			'data' => $data
		], $code);
	}

	public function error($errors = [],$message = null, $code = 400)
	{
		return response()->json([
			'status'=> false,
			'message' => $message,
			'data' => $errors
		], $code);
	}

}
