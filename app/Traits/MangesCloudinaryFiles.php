<?php

namespace App\Traits;

use Cloudinary\Cloudinary;

trait MangesCloudinaryFiles
{
    protected function uploadImage($file,string $folder ="default"): array
    {
        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

        $uploadedFile = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'image',
        ]);

        return [
            'url' => $uploadedFile['secure_url'],
            'public_id' => $uploadedFile['public_id'],
        ];
    }

    protected function deleteImage(string $publicId):void
    {
        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
        $cloudinary->uploadApi()->destroy($publicId);
    }


}
