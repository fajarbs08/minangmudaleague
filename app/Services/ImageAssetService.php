<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageAssetService
{
    public function storeLogo(UploadedFile $file, string $directory = 'club-logos'): string
    {
        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'square',
                'canvas_size' => 512,
                'padding' => 16,
            ]
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
            ]
        );
    }

    public function storeDocumentUpload(UploadedFile $file, string $directory): string
    {
        if (!$this->isImageUpload($file)) {
            return $file->store($directory, 'public');
        }

        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'contain',
                'max_width' => 2800,
                'max_height' => 2800,
            ]
        );
    }

    public function storeResourceUpload(UploadedFile $file, string $directory): string
    {
        if (!$this->isImageUpload($file)) {
            return $file->store($directory, 'public');
        }

        return $this->storeBinary(
            $this->readUploadedFile($file),
            $directory,
            [
                'mode' => 'contain',
                'max_width' => 2200,
                'max_height' => 2200,
            ]
        );
    }

    public function normalizeStoredPathIfImage(string $path, string $directory, array $profile): ?string
    {
        $normalizedPath = ltrim($path, '/');
        $disk = Storage::disk('public');

        if (!$disk->exists($normalizedPath)) {
            throw new RuntimeException('File sumber tidak ditemukan pada penyimpanan publik.');
        }

        $mime = (string) ($disk->mimeType($normalizedPath) ?: '');
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));

        if (!$this->isImageMime($mime) && $extension !== 'svg') {
            return null;
        }

        $binary = $disk->get($normalizedPath);

        if ($binary === false) {
            throw new RuntimeException('File sumber tidak dapat dibaca dari penyimpanan.');
        }

        return $this->storeBinary($binary, $directory, $profile);
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

    private function readUploadedFile(UploadedFile $file): string
    {
        $binary = file_get_contents($file->getRealPath());

        if ($binary === false) {
            throw new RuntimeException('File gambar tidak dapat dibaca.');
        }

        return $binary;
    }

    private function storeBinary(string $binary, string $directory, array $profile): string
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

        Storage::disk('public')->put($path, $pngBinary);

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
