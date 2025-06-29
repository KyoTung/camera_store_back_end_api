<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage as FirebaseStorage;
use Kreait\Firebase\ServiceAccount;

class FirebaseStorageService
{
    protected $storage;
    protected $bucket;

    public function __construct()
    {
        $credentialsJson = env('FIREBASE_CREDENTIALS');

        if (!$credentialsJson) {
            throw new \Exception('Firebase credentials not found');
        }

        $serviceAccount = ServiceAccount::fromValue(json_decode($credentialsJson, true));

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount);

        $this->storage = $firebase->createStorage();
        $this->bucket = $this->storage->getBucket(env('FIREBASE_STORAGE_BUCKET'));
    }

    public function uploadImage($image, $directory)
    {
        $imageName = $directory . '/' . uniqid() . '.' . $image->getClientOriginalExtension();

        $stream = fopen($image->getRealPath(), 'r');

        $object = $this->bucket->upload($stream, [
            'name' => $imageName
        ]);

        return $imageName;
    }

    public function deleteImage($path)
    {
        $object = $this->bucket->object($path);
        if ($object->exists()) {
            $object->delete();
        }
    }

    public function getImageUrl($path)
    {
        $object = $this->bucket->object($path);
        return $object->signedUrl(now()->addYears(10));
    }
}
