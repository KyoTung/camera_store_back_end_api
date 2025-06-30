<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url'];


    function  product_images()
    {
       return $this->hasMany(ProductImage::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        return app(\App\Services\FirebaseStorageService::class)
            ->getSignedUrl($this->image);
    }
}
