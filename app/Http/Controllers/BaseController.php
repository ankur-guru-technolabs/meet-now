<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPhoto;
use Illuminate\Support\Facades\File;

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

	public function uploadMediaFiles($mediaFiles, $userId)
	{
		$user_photo_data = [];
	
		foreach ($mediaFiles as $media) {
			$extension = $media->getClientOriginalExtension();
			$filename = 'User_' . $userId . '_' . random_int(10000, 99999) . '.' . $extension;
			$media->move(public_path('user_profile'), $filename);
	
			$type = $this->getMediaType($extension);
	
			$user_photo_data[] = [
				'user_id' => $userId,
				'name' => $filename,
				'type' => $type,
				'created_at' => now(),
				'updated_at' => now()
			];
		}
	
		return $user_photo_data;
	}

	public function uploadImageFile($file, $userId, $type)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'User_' . $userId . '_' . random_int(10000, 99999) . '.' . $extension;
        $file->move(public_path('user_profile'), $filename);
        return [
            'user_id' => $userId,
            'name' => $filename,
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function getMediaType($extension)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png'];
        $videoExtensions = ['mp4', 'mov', 'avi'];
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        }
    } 

	public function deleteUserPhotos($imageIds, $userId, $type)
	{
		$userPhotos = UserPhoto::where('user_id', $userId)->where('type', $type);
		if (!empty($imageIds)) {
			$userPhotos->whereIn('id', $imageIds);
		}
		$photoNames = $userPhotos->pluck('name')->toArray();
		foreach ($photoNames as $name) {
			$path = public_path('user_profile/' . $name);
			if (File::exists($path)) {
				if (!is_writable($path)) {
					chmod($path, 0777);
				}
				File::delete($path);
			}
		}
		$userPhotos->delete();
	}
}
