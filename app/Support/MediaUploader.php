<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaUploader
{
    /**
     * @return array{provider:string,path:string,url:string}
     */
    public function uploadImage(UploadedFile $file, string $folder): array
    {
        $folder = $this->normalizeFolder($folder);
        $credentials = $this->cloudinaryCredentials();

        if ($credentials !== null) {
            return $this->uploadToCloudinary($file, $folder, $credentials);
        }

        return $this->uploadToPublicDisk($file, $folder);
    }

    public function isAbsoluteUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return (bool) preg_match('/^https?:\/\//i', $value);
    }

    public function deleteImage(?string $publicId): void
    {
        $publicId = trim((string) $publicId);

        if ($publicId === '' || $this->isAbsoluteUrl($publicId)) {
            return;
        }

        $credentials = $this->cloudinaryCredentials();
        if ($credentials === null) {
            return;
        }

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => (string) $timestamp,
        ];

        ksort($params);
        $signatureBase = collect($params)
            ->map(fn (string $value, string $key) => $key . '=' . $value)
            ->implode('&');

        $signature = sha1($signatureBase . $credentials['api_secret']);
        $endpoint = 'https://api.cloudinary.com/v1_1/' . $credentials['cloud_name'] . '/image/destroy';

        Http::timeout(30)
            ->asForm()
            ->post($endpoint, [
                'api_key' => $credentials['api_key'],
                'signature' => $signature,
                'timestamp' => $timestamp,
                'public_id' => $publicId,
                'invalidate' => true,
            ]);
    }

    /**
     * @param array{cloud_name:string,api_key:string,api_secret:string} $credentials
     * @return array{provider:string,path:string,url:string}
     */
    private function uploadToCloudinary(UploadedFile $file, string $folder, array $credentials): array
    {
        $timestamp = time();
        $params = [
            'folder' => $folder,
            'timestamp' => (string) $timestamp,
        ];

        ksort($params);
        $signatureBase = collect($params)
            ->map(fn (string $value, string $key) => $key . '=' . $value)
            ->implode('&');

        $signature = sha1($signatureBase . $credentials['api_secret']);
        $endpoint = 'https://api.cloudinary.com/v1_1/' . $credentials['cloud_name'] . '/image/upload';

        $response = Http::timeout(45)
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->post($endpoint, [
                'api_key' => $credentials['api_key'],
                'signature' => $signature,
                'timestamp' => $timestamp,
                'folder' => $folder,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Cloudinary upload failed: ' . $response->body());
        }

        $json = $response->json();
        $secureUrl = $json['secure_url'] ?? null;
        $publicId = $json['public_id'] ?? null;

        if (!$secureUrl || !$publicId) {
            throw new RuntimeException('Cloudinary response missing secure_url/public_id');
        }

        return [
            'provider' => 'cloudinary',
            'path' => (string) $publicId,
            'url' => (string) $secureUrl,
        ];
    }

    /**
     * @return array{provider:string,path:string,url:string}
     */
    private function uploadToPublicDisk(UploadedFile $file, string $folder): array
    {
        $path = $file->store($folder, 'public');

        return [
            'provider' => 'local',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }

    private function normalizeFolder(string $folder): string
    {
        $folder = trim($folder, '/');
        $base = trim((string) config('services.cloudinary.folder_base', 'comercioplus'), '/');

        if ($base === '') {
            return $folder;
        }

        if ($folder === '') {
            return $base;
        }

        if (str_starts_with($folder, $base . '/')) {
            return $folder;
        }

        return $base . '/' . $folder;
    }

    /**
     * @return array{cloud_name:string,api_key:string,api_secret:string}|null
     */
    private function cloudinaryCredentials(): ?array
    {
        $cloudName = trim((string) config('services.cloudinary.cloud_name', ''));
        $apiKey = trim((string) config('services.cloudinary.api_key', ''));
        $apiSecret = trim((string) config('services.cloudinary.api_secret', ''));

        if ($cloudName !== '' && $apiKey !== '' && $apiSecret !== '') {
            return [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ];
        }

        $cloudinaryUrl = trim((string) config('services.cloudinary.url', ''));
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
