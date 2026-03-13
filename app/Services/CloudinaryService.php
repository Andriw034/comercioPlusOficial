<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CloudinaryService
{
    private ?array $credentials = null;

    public function __construct()
    {
        $this->credentials = $this->resolveCredentials();

        if ($this->credentials !== null) {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $this->credentials['cloud_name'],
                    'api_key' => $this->credentials['api_key'],
                    'api_secret' => $this->credentials['api_secret'],
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);
        }
    }

    /**
     * @return array{
     *   url:string,
     *   secure_url:string,
     *   public_id:string,
     *   width:int|null,
     *   height:int|null,
     *   provider:string,
     *   path:string
     * }
     */
    public function uploadImage(UploadedFile $file, string $folder): array
    {
        if ($this->credentials !== null) {
            return $this->uploadToCloudinary($file, $folder);
        }

        return $this->uploadToLocalDisk($file, $folder);
    }

    public function isAbsoluteUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return (bool) preg_match('/^https?:\/\//i', $value);
    }

    /**
     * @return array{
     *   url:string,
     *   secure_url:string,
     *   public_id:string,
     *   width:int|null,
     *   height:int|null,
     *   provider:string,
     *   path:string
     * }
     */
    private function uploadToCloudinary(UploadedFile $file, string $folder): array
    {
        try {
            $response = (new UploadApi())->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'image',
                'use_filename' => true,
                'unique_filename' => true,
                'overwrite' => false,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Cloudinary upload failed: ' . $e->getMessage(), 0, $e);
        }

        $secureUrl = (string) ($response['secure_url'] ?? '');
        $publicId = (string) ($response['public_id'] ?? '');

        if ($secureUrl === '' || $publicId === '') {
            throw new RuntimeException('Cloudinary response missing secure_url/public_id');
        }

        return [
            'url' => $secureUrl,
            'secure_url' => $secureUrl,
            'public_id' => $publicId,
            'width' => isset($response['width']) ? (int) $response['width'] : null,
            'height' => isset($response['height']) ? (int) $response['height'] : null,
            'provider' => 'cloudinary',
            'path' => $publicId,
        ];
    }

    /**
     * @return array{
     *   url:string,
     *   secure_url:string,
     *   public_id:string,
     *   width:int|null,
     *   height:int|null,
     *   provider:string,
     *   path:string
     * }
     */
    private function uploadToLocalDisk(UploadedFile $file, string $folder): array
    {
        $path = $file->store($folder, 'public');
        $url = (string) Storage::disk('public')->url($path);

        return [
            'url' => $url,
            'secure_url' => $url,
            'public_id' => $path,
            'width' => null,
            'height' => null,
            'provider' => 'local',
            'path' => $path,
        ];
    }

    /**
     * @return array{cloud_name:string,api_key:string,api_secret:string}|null
     */
    private function resolveCredentials(): ?array
    {
        $cloudName = trim((string) config('cloudinary.cloud_name', config('services.cloudinary.cloud_name', '')));
        $apiKey = trim((string) config('cloudinary.api_key', config('services.cloudinary.api_key', '')));
        $apiSecret = trim((string) config('cloudinary.api_secret', config('services.cloudinary.api_secret', '')));

        if ($cloudName !== '' && $apiKey !== '' && $apiSecret !== '') {
            return [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ];
        }

        $cloudinaryUrl = trim((string) config('cloudinary.url', config('services.cloudinary.url', '')));
        if ($cloudinaryUrl === '') {
            return null;
        }

        $parts = parse_url($cloudinaryUrl);
        if (!$parts || !isset($parts['host'], $parts['user'], $parts['pass'])) {
            return null;
        }

        return [
            'cloud_name' => $parts['host'],
            'api_key' => $parts['user'],
            'api_secret' => $parts['pass'],
        ];
    }
}

