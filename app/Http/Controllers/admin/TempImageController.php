<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class TempImageController extends Controller
{
    public  function  store(Request $request)
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

        $tempImage = new TempImage();
        $tempImage->name = "Image name";
        $tempImage->save();

        $image = $request->file('image');
        $imageName = time().'.'.$image->extension();
        $image->move(public_path('uploads/temp'),$imageName);

        $tempImage->name = $imageName;
        $tempImage->save();


        //save image thumbnail
        $manager = new ImageManager(Driver::class);
        $img = $manager->read(public_path('uploads/temp/'.$imageName));
        $img->coverDown(600,650);
        $img->save(public_path('uploads/temp/thumb/'.$imageName));


        return response()->json([
            'data'=>$tempImage,
            'message'=>'Image added successfully',
            'status'=>200,
        ], status: 200);
    }
}
