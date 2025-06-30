<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\StorageException;
use Kreait\Firebase\Storage;
use Google\Cloud\Storage\StorageObject;

class FirebaseStorageService
{
    protected $storage;
    protected $bucket;

    public function __construct()
    {
        $credentialsJson = env('FIREBASE_CREDENTIALS');
        $storageBucket = env('FIREBASE_STORAGE_BUCKET');

        if (!$credentialsJson || !$storageBucket) {
            throw new \Exception('Firebase configuration is missing in environment variables');
        }

        try {
            $serviceAccount = json_decode($credentialsJson, true);

            $factory = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDefaultStorageBucket($storageBucket);

            $this->storage = $factory->createStorage();
            $this->bucket = $this->storage->getBucket();
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('Invalid Firebase credentials: ' . $e->getMessage());
        } catch (FirebaseException $e) {
            throw new \Exception('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload a file from local path
     *
     * @param string $localFilePath Local file path
     * @param string $destinationPath Path in Firebase Storage
     * @return string Firebase storage path
     * @throws StorageException
     */
    public function uploadFile(string $localFilePath, string $destinationPath): string
    {
        if (!file_exists($localFilePath)) {
            throw new \Exception("File not found: $localFilePath");
        }

        try {
            $this->bucket->upload(
                fopen($localFilePath, 'r'),
                [
                    'name' => $destinationPath,
                    'predefinedAcl' => 'publicRead',
                ]
            );
            return $destinationPath;
        } catch (StorageException $e) {
            throw new StorageException('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file from Firebase Storage
     *
     * @param string $filePath Path in Firebase Storage
     * @return bool
     * @throws StorageException
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            $object = $this->bucket->object($filePath);
            if ($object->exists()) {
                $object->delete();
                return true;
            }
            return false;
        } catch (StorageException $e) {
            throw new StorageException('Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a signed URL for a file
     *
     * @param string $filePath Path in Firebase Storage
     * @param \DateTimeInterface|int $expiration Expiration time (default: 10 years)
     * @return string
     * @throws StorageException
     */
    public function getSignedUrl(string $filePath, $expiration = null): string
    {
        try {
            $object = $this->bucket->object($filePath);

            if ($expiration === null) {
                $expiration = new \DateTime('+10 years');
            } elseif (is_int($expiration)) {
                $expiration = new \DateTime("+$expiration minutes");
            }

            return $object->signedUrl($expiration);
        } catch (StorageException $e) {
            throw new StorageException('URL generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the public URL for a file
     *
     * @param string $filePath Path in Firebase Storage
     * @return string
     */
    public function getPublicUrl(string $filePath): string
    {
        return sprintf(
            'https://storage.googleapis.com/%s/%s',
            $this->bucket->name(),
            $filePath
        );
    }
//    public function getImageUrl(string $filePath): string
//    {
//        // Sử dụng URL có chữ ký (signed URL)
//        return $this->getSignedUrl($filePath);
//
//        // Hoặc nếu bạn muốn URL công khai:
//        // return $this->getPublicUrl($filePath);
//    }
    /**
     * Check if a file exists
     *
     * @param string $filePath Path in Firebase Storage
     * @return bool
     */
    public function fileExists(string $filePath): bool
    {
        try {
            return $this->bucket->object($filePath)->exists();
        } catch (StorageException $e) {
            return false;
        }
    }
}
