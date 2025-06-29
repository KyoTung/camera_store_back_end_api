<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Faker\Core\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use App\Services\FirebaseStorageService;

class ProductController extends Controller
{
    protected $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->firebaseStorage = $firebaseStorage;
    }
    public function index()
    {
        $product = Product::orderBy('created_at', 'DESC')
            ->with('product_images')
            ->get();

        return response()->json([
            'data'=>$product,
            'status'=>200,
            'message'=>"Get all products successfully"
        ]);
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'status' => 'required|integer',
            'is_featured' => 'required|in:yes,no',
            'sku' => 'nullable|unique:products,sku|string|max:255',

            'resolution' => 'nullable|string|max:255',
            'infrared' => 'nullable|string|max:255',
            'sound' => 'nullable|string|max:255',
            'smart_function' => 'nullable|string|max:255',
            'AI_function' => 'nullable|string|max:255',
            'network' => 'nullable|string|max:255',
            'other_features' => 'nullable|string|max:255',

            'category' => 'required|integer',
            'brand' => 'required|integer',
            'gallery' => 'nullable|array',
            'gallery.*' => 'numeric|exists:temp_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>403,
                'errors'=>$validator->errors(),
            ], status: 403);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->quantity = $request->quantity;
        $product->image = $request->image;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->sku = $request->sku;
        // product parameter
        $product->resolution = $request->resolution;
        $product->infrared = $request->infrared;
        $product->sound = $request->sound;
        $product->smart_function = $request->smart_function;
        $product->AI_function = $request->AI_function;
        $product->network = $request->network;
        $product->other_features = $request->other_features;
        //fk key
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;

        $product->save();

        // Xử lý ảnh gallery
        if(!empty($request->gallery)){
            foreach ($request->gallery as $key => $tempImageId){
                $tempImage = TempImage::find($tempImageId);

                if($tempImage) {
                    $image = $this->firebaseStorage->uploadImage(
                        new \Illuminate\Http\UploadedFile(
                            public_path('uploads/temp/'.$tempImage->name),
                            $tempImage->name
                        ),
                        'products'
                    );

                    $productImage = new ProductImage();
                    $productImage->image = $image;
                    $productImage->product_id = $product->id;
                    $productImage->save();

                    if($key == 0){
                        $product->image = $image;
                        $product->save();
                    }
                }
            }
        }

        return response()->json([
            'data'=>$product,
            'message'=>'Product added successfully',
            'status'=>200,
        ], status: 200);
    }

    public function show($id)
    {
        $product = Product::with('product_images')->find($id);

        if( $product == null){
            return response()->json([
                'message'=>'Product not found',
                'data'=>[],
                'status'=>404,
            ],  404);
        }

        return response()->json([
            'data'=>$product,
            'message'=>'Get a product successfully',
            'status'=>200,
        ], status: 200);
    }

    public function update(Request $request, $id)
    {

        $product = Product::find($id);

        if( $product == null){
            return response()->json([
                'message'=>'Product not found',
                'data'=>[],
                'status'=>404,
            ],  404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
//            'image' => 'nullable|string',
            'status' => 'required|integer',
            'is_featured' => 'required|in:yes,no',
            'sku' => 'nullable|string|max:255,'.$id.',id',
            'resolution' => 'nullable|string|max:255',
            'infrared' => 'nullable|string|max:255',
            'sound' => 'nullable|string|max:255',
            'smart_function' => 'nullable|string|max:255',
            'AI_function' => 'nullable|string|max:255',
            'network' => 'nullable|string|max:255',
            'other_features' => 'nullable|string|max:255',
            'category_id' => 'required|integer',
            'brand_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>403,
                'errors'=>$validator->errors(),
            ], status: 403);
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->quantity = $request->quantity;
//        $product->image = $request->image;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->sku = $request->sku;
        // product parameter
        $product->resolution = $request->resolution;
        $product->infrared = $request->infrared;
        $product->sound = $request->sound;
        $product->smart_function = $request->smart_function;
        $product->AI_function = $request->AI_function;
        $product->network = $request->network;
        $product->other_features = $request->other_features;
        //fk key
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $product->save();

        return response()->json([
            'data'=>$product,
            'message'=>'Product updated successfully',
            'status'=>200,
        ], status: 200);
    }

    public function updateDefaultImage(Request $request)
    {
        $product = Product::find($request->product_id);
        $product->image = $request->image;
        $product->save();

        return response()->json([
            'message'=>'Product default image changed successfully',
            'status'=>200,
        ], status: 200);
    }

    public function saveProductImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>403,
                'message'=>'Upload image fail',
                'errors'=>$validator->errors(),
            ], 403);
        }

        // Upload ảnh lên Firebase
        $imagePath = $this->firebaseStorage->uploadImage(
            $request->file('image'),
            'products'
        );

        $productImage = new ProductImage();
        $productImage->image = $imagePath;
        $productImage->product_id = $id;
        $productImage->save();

        return response()->json([
            'data'=>$productImage,
            'message'=>'Image added successfully',
            'status'=>200,
        ], 200);
    }

    public function deleteProductImage($id)
    {
        $productImage = ProductImage::find($id);

        if(!$productImage){
            return response()->json([
                'message'=>'Image not found',
                'status'=>404,
            ], 404);
        }

        // Xóa ảnh từ Firebase
        $this->firebaseStorage->deleteImage($productImage->image);
        $productImage->delete();

        return response()->json([
            'message'=>'Product image deleted successfully',
            'status'=>200,
        ], 200);
    }

    public function destroy($id)
    {
        $product = Product::with('product_images')->find($id);

        if(!$product){
            return response()->json([
                'message'=>'Product not found',
                'data'=>[],
                'status'=>404,
            ], 404);
        }

        // Xóa tất cả ảnh liên quan
        foreach ($product->product_images as $productImage) {
            $this->firebaseStorage->deleteImage($productImage->image);
        }

        $product->delete();

        return response()->json([
            'message'=>'Product deleted successfully',
            'status'=>200,
        ], 200);
    }
}
