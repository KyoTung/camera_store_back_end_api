<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return app(FirebaseStorageService::class)->getSignedUrl($this->image);
    }
}
