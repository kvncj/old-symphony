<?php

namespace App\Service;

use Google\Cloud\Storage\StorageClient;

class ImageService
{
    private StorageClient $gStorage;

    public function __construct(string $projectDir)
    {
        $serviceAccountPath = $projectDir . "/service-account.json";
        $this->gStorage = new StorageClient([
            'keyFilePath' => $serviceAccountPath
        ]);
    }

    public function getExtFromMime($mimeType)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];
        if(!array_key_exists($mimeType, $extensions))
            throw new \Exception("Invalid image format: $mimeType");
        return $extensions[$mimeType];
    }

    public function getMimeFromExt($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        ];
        if(!array_key_exists($extension, $mimeTypes))
            return 'image/jpeg';//throw new \Exception("Invalid image format: $extension");
        return $mimeTypes[$extension];
    }

    public function getLink($path)
    {
        $encodePath = rawurlencode($path);
        return "https://storage.googleapis.com/pandorabox_wpbuckets/$encodePath";
    }

    public function deleteImage(string $path): void
    {
        $bucket = $this->gStorage->bucket('pandorabox_wpbuckets');
        $image = $bucket->object($path);
        try {
            $image->delete();
        } catch (\Google\Cloud\Core\Exception\NotFoundException $e) {
            throw $e;
            return;
        }
    }

    public function uploadImage(string $name, string $base64, string $folder = 'image')
    {
        $path = "$folder/" . date('Y') . '/' . date('m') . '/';
        $bucket = $this->gStorage->bucket('pandorabox_wpbuckets');

        $bucket->upload(fopen($base64, 'r'), [
            'name' => $path . $name,
            'predefinedAcl' => 'publicRead'
        ]);

        return $path . $name;
    }
}
