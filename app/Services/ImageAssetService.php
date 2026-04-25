<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageAssetService
{
    private const PRIVATE_DISK = 'local';

    private const PUBLIC_DISK = 'public';

    public function storeLogo(UploadedFile $file, string $directory = 'club-logos'): string
    {
        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'square',
                'canvas_size' => 512,
                'padding' => 16,
            ],
            self::PUBLIC_DISK
        );
    }

    public function storePhoto(UploadedFile $file, string $directory): string
    {
        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'contain',
                'max_width' => 1600,
                'max_height' => 1600,
            ],
            self::PUBLIC_DISK
        );
    }

    public function storeDocumentUpload(UploadedFile $file, string $directory): string
    {
        if (! $this->isImageUpload($file)) {
            return $file->store($directory, self::PRIVATE_DISK);
        }

        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'contain',
                'max_width' => 2800,
                'max_height' => 2800,
            ],
            self::PRIVATE_DISK
        );
    }

    public function storeResourceUpload(UploadedFile $file, string $directory): string
    {
        if (! $this->isImageUpload($file)) {
            return $file->store($directory, self::PUBLIC_DISK);
        }

        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'contain',
                'max_width' => 2200,
                'max_height' => 2200,
            ],
            self::PUBLIC_DISK
        );
    }

    public function normalizeStoredPathIfImage(string $path, string $directory, array $profile, string $disk = self::PUBLIC_DISK): ?string
    {
        $normalizedPath = ltrim($path, '/');

        $sourceDisk = $this->resolveStoredDisk($normalizedPath, [$disk, self::PUBLIC_DISK, self::PRIVATE_DISK]);

        if ($sourceDisk === null) {
            throw new RuntimeException('File sumber tidak ditemukan pada penyimpanan.');
        }

        $storage = Storage::disk($sourceDisk);

        $mime = (string) ($storage->mimeType($normalizedPath) ?: '');
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));

        if (! $this->isImageMime($mime) && $extension !== 'svg') {
            return null;
        }

        $binary = $storage->get($normalizedPath);

        if ($binary === false) {
            throw new RuntimeException('File sumber tidak dapat dibaca dari penyimpanan.');
        }

        return $this->storeBinary($binary, $directory, $profile, $disk);
    }

    public function documentAbsolutePath(?string $path): ?string
    {
        $location = $this->documentStorageLocation($path);

        if ($location === null) {
            return null;
        }

        return Storage::disk($location['disk'])->path($location['path']);
    }

    public function documentExists(?string $path): bool
    {
        return $this->documentStorageLocation($path) !== null;
    }

    public function deleteDocumentUpload(?string $path): void
    {
        $path = $this->normalizeManagedPath($path);

        if ($path === null) {
            return;
        }

        foreach ([self::PRIVATE_DISK, self::PUBLIC_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    public function moveDocumentUploadToPrivateDisk(?string $path): bool
    {
        $path = $this->normalizeManagedPath($path);

        if ($path === null) {
            return false;
        }

        $privateDisk = Storage::disk(self::PRIVATE_DISK);
        $publicDisk = Storage::disk(self::PUBLIC_DISK);

        if ($privateDisk->exists($path)) {
            if ($publicDisk->exists($path)) {
                $publicDisk->delete($path);
            }

            return true;
        }

        if (! $publicDisk->exists($path)) {
            return false;
        }

        $binary = $publicDisk->get($path);

        if ($binary === false) {
            throw new RuntimeException('Dokumen sensitif tidak dapat dibaca dari penyimpanan publik.');
        }

        $privateDisk->put($path, $binary);
        $publicDisk->delete($path);

        return true;
    }

    public function isExternalPath(?string $path): bool
    {
        return filled($path) && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
    }

    public function isNormalizedPath(?string $path, string $directory): bool
    {
        if (blank($path)) {
            return false;
        }

        $directory = trim($directory, '/');

        return (bool) preg_match('#^'.preg_quote($directory, '#').'/\d{4}/\d{2}/[A-Za-z0-9_-]+_[A-Za-z0-9._-]+\.png$#', ltrim((string) $path, '/'));
    }

    public function isImageUpload(UploadedFile $file): bool
    {
        $mime = (string) ($file->getMimeType() ?: '');
        $extension = strtolower($file->getClientOriginalExtension());

        return $this->isImageMime($mime) || $extension === 'svg';
    }

    private function isImageMime(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    private function documentStorageLocation(?string $path): ?array
    {
        $path = $this->normalizeManagedPath($path);

        if ($path === null) {
            return null;
        }

        $disk = $this->resolveStoredDisk($path, [self::PRIVATE_DISK, self::PUBLIC_DISK]);

        if ($disk === null) {
            return null;
        }

        return [
            'disk' => $disk,
            'path' => $path,
        ];
    }

    private function normalizeManagedPath(?string $path): ?string
    {
        if (blank($path) || $this->isExternalPath($path)) {
            return null;
        }

        return ltrim((string) $path, '/');
    }

    private function resolveStoredDisk(string $path, array $candidateDisks): ?string
    {
        foreach (array_unique($candidateDisks) as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    private function readUploadedFile(UploadedFile $file): string
    {
        $binary = file_get_contents($file->getRealPath());

        if ($binary === false) {
            throw new RuntimeException('File gambar tidak dapat dibaca.');
        }

        return $binary;
    }

    private function storeBinary(string $binary, string $directory, array $profile, string $disk = self::PUBLIC_DISK): string
    {
        $source = $this->createSourceFromBinary($binary);

        if ($source === false) {
            throw new RuntimeException('Format gambar tidak didukung untuk diproses.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($source);

            throw new RuntimeException('Ukuran gambar tidak valid.');
        }

        if (($profile['mode'] ?? 'contain') === 'square') {
            $canvasSize = (int) ($profile['canvas_size'] ?? 512);
            $padding = (int) ($profile['padding'] ?? 0);
            $targetMax = max(1, $canvasSize - ($padding * 2));
            $scale = min($targetMax / $sourceWidth, $targetMax / $sourceHeight);

            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $canvasWidth = $canvasSize;
            $canvasHeight = $canvasSize;
            $targetX = (int) floor(($canvasWidth - $targetWidth) / 2);
            $targetY = (int) floor(($canvasHeight - $targetHeight) / 2);
        } else {
            $maxWidth = max(1, (int) ($profile['max_width'] ?? $sourceWidth));
            $maxHeight = max(1, (int) ($profile['max_height'] ?? $sourceHeight));
            $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);

            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $canvasWidth = $targetWidth;
            $canvasHeight = $targetHeight;
            $targetX = 0;
            $targetY = 0;
        }

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        if ($canvas === false) {
            imagedestroy($source);

            throw new RuntimeException('Kanvas gambar tidak dapat dibuat.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, $transparent);

        imagealphablending($canvas, true);
        imagecopyresampled(
            $canvas,
            $source,
            $targetX,
            $targetY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();
        imagepng($canvas, null, 6);
        $pngBinary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($pngBinary === false) {
            throw new RuntimeException('Gambar standar tidak dapat dibuat.');
        }

        $directory = trim($directory, '/');
        $path = $directory.'/'.now()->format('Y/m').'/'.str_replace('/', '_', $directory).'_'.uniqid('', true).'.png';

        Storage::disk($disk)->put($path, $pngBinary);

        return $path;
    }

    private function createSourceFromBinary(string $binary): mixed
    {
        $source = @imagecreatefromstring($binary);

        if ($source !== false) {
            return $source;
        }

        if (!class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $imagick = new \Imagick();
            $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
            $imagick->setResolution(300, 300);
            $imagick->readImageBlob($binary);
            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
            $imagick->setImageBackgroundColor(new \ImagickPixel('transparent'));
            $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE);
            $imagick->setImageFormat('png32');

            $renderedBinary = $imagick->getImagesBlob();
            $imagick->clear();
            $imagick->destroy();

            return @imagecreatefromstring($renderedBinary);
        } catch (\Throwable) {
            return false;
        }
    }
}
