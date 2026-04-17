<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ClubLogoService
{
    public function __construct(private ImageAssetService $imageAssetService)
    {
    }

    public function storeNormalized(UploadedFile $file): string
    {
        return $this->imageAssetService->storeLogo($file, 'club-logos');
    }

    public function normalizeStoredPath(string $path): string
    {
        return $this->imageAssetService->normalizeStoredPathIfImage($path, 'club-logos', [
            'mode' => 'square',
            'canvas_size' => 512,
            'padding' => 16,
        ]) ?? $path;
    }

    public function isNormalizedPath(?string $path): bool
    {
        return $this->imageAssetService->isNormalizedPath($path, 'club-logos');
    }
}
