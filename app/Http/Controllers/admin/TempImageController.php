<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class TempImageController extends Controller
{
    protected $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->firebaseStorage = $firebaseStorage;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>403,
                'errors'=>$validator->errors(),
            ], 403);
        }

        // Tạo bản ghi tạm
        $tempImage = new TempImage();
        $tempImage->save(); // Lưu để có ID

        // Xử lý ảnh
        $image = $request->file('image');
        $originalName = $image->getClientOriginalName();
        $extension = $image->getClientOriginalExtension();

        // Tạo tên file với ID của tempImage
        $imageName = 'temp/' . $tempImage->id . '.' . $extension;

        // Tạo thumbnail trong bộ nhớ
        $manager = new ImageManager(Driver::class);
        $img = $manager->read($image->getPathname());
        $img->coverDown(600, 650);
        $thumbnail = $img->encodeByMediaType('image/jpeg', 80);

        // Upload ảnh gốc và thumbnail lên Firebase
        $originalPath = $this->firebaseStorage->uploadFile(
            $image->getPathname(),
            'temp/' . $tempImage->id . '-original.' . $extension
        );

        $thumbnailPath = $this->firebaseStorage->uploadFromMemory(
            $thumbnail,
            'temp/' . $tempImage->id . '-thumbnail.' . $extension
        );

        // Cập nhật bản ghi tạm
        $tempImage->name = $originalPath;
        $tempImage->thumbnail = $thumbnailPath;
        $tempImage->save();

        return response()->json([
            'data' => [
                'id' => $tempImage->id,
                'name' => $originalPath,
                'thumbnail' => $thumbnailPath,
                'url' => $this->firebaseStorage->getImageUrl($thumbnailPath)
            ],
            'message' => 'Image added successfully',
            'status' => 200,
        ], 200);
    }
}
