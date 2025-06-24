<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>403,
                'errors'=>$validator->errors(),
            ], status: 403);
        }

        $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'temp_images'
        ]);
        $url = $uploadedFile->getSecurePath();
        $publicId = $uploadedFile->getPublicId();
        $thumbUrl = Cloudinary::getUrl($publicId, [
            'width'=>600,
            'height'=>650,
            'crop'=>'fill'
        ]);

        $tempImage = new TempImage();
        $tempImage->name = $url;
        $tempImage->cloudinary_public_id = $publicId;
        $tempImage->save();

        return response()->json([
            'data'=>[
                'id' => $tempImage->id,
                'image_url' => $url,
                'thumb_url' => $thumbUrl,
                'cloudinary_public_id' => $publicId
            ],
            'message'=>'Image uploaded to Cloudinary successfully',
            'status'=>200,
        ], status: 200);
    }
}
