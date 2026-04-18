<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

trait SeedsDemoAssets
{
    protected function seedDemoDocument(string $filename, string $title): string
    {
        $path = str_contains($filename, '/')
            ? $filename
            : 'demo-documents/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->makeSimplePdf($title));
        }

        return $path;
    }

    protected function seedDemoImage(string $filename, string $title): string
    {
        $path = str_contains($filename, '/')
            ? $filename
            : 'demo-images/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <rect width="600" height="600" fill="#eef3ff"/>
  <circle cx="300" cy="220" r="110" fill="#1f5ea8"/>
  <rect x="120" y="360" width="360" height="120" rx="24" fill="#dbe7ff"/>
  <text x="300" y="545" font-size="34" text-anchor="middle" fill="#163861" font-family="Arial, sans-serif">{$safeTitle}</text>
</svg>
SVG;
            Storage::disk('public')->put($path, $svg);
        }

        return $path;
    }

    protected function makeSimplePdf(string $title): string
    {
        $safeTitle = substr(preg_replace('/[^A-Za-z0-9 .-]/', '', $title) ?: 'DSP Demo', 0, 60);
        $stream = "BT /F1 18 Tf 72 720 Td ({$safeTitle}) Tj ET";
        $length = strlen($stream);

        return "%PDF-1.4\n".
            "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n".
            "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n".
            "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj\n".
            "4 0 obj<< /Length {$length} >>stream\n{$stream}\nendstream\nendobj\n".
            "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n".
            "xref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000063 00000 n \n0000000122 00000 n \n0000000248 00000 n \n0000000341 00000 n \n".
            "trailer<< /Root 1 0 R /Size 6 >>\nstartxref\n411\n%%EOF";
    }
}
