<?php

namespace App\Services\IdCards;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdentityCardService
{
    public const DEFAULT_BATCH_EXPORT_LIMIT = 64;

    private const PDF_TEMPLATE_VERSION = 'idc-template-v31';

    private const PDF_RENDER_TIMEOUT_SECONDS = 180;

    private const MAX_BATCH_EXPORT_LIMIT = 160;

    private const QR_IMAGE_SIZE = 420;

    private const EMBED_PROFILE_BRAND_LOGO = [
        'max_width' => 96,
        'max_height' => 96,
        'format' => 'png',
    ];

    private const EMBED_PROFILE_WATERMARK = [
        'max_width' => 240,
        'max_height' => 240,
        'format' => 'png',
    ];

    private const EMBED_PROFILE_PERSON_PHOTO = [
        'max_width' => 260,
        'max_height' => 320,
        'format' => 'jpeg',
        'quality' => 82,
    ];

    private array $imageSourceCache = [];

    private array $qrSourceCache = [];

    public function buildOfficialDocument(Club $club, AgeGroup $ageGroup, iterable $officials): array
    {
        return $this->buildDocument(
            subjectType: 'official',
            title: 'ID Card Official',
            club: $club,
            ageGroup: $ageGroup,
            records: $officials,
            cardBuilder: fn (Official $official) => $this->buildOfficialCard($official, $club, $ageGroup),
        );
    }

    public function buildPlayerDocument(Club $club, AgeGroup $ageGroup, iterable $players): array
    {
        return $this->buildDocument(
            subjectType: 'player',
            title: 'ID Card Pemain',
            club: $club,
            ageGroup: $ageGroup,
            records: $players,
            cardBuilder: fn (Player $player) => $this->buildPlayerCard($player, $club, $ageGroup),
        );
    }

    public function pdfResponse(array $document, string $filename, bool $download = false): Response
    {
        $pdf = $this->renderPdf($document);

        return response($pdf, 200, $this->pdfHeaders($filename, $download));
    }

    public function pdfResponseCached(array $document, string $filename, string $cacheKey, bool $download = false): Response
    {
        $disk = Storage::disk('local');
        $cacheDir = 'id-cards-cache';
        $versionedCacheKey = self::PDF_TEMPLATE_VERSION.'|'.$cacheKey;
        $cacheFile = $cacheDir.'/'.sha1($versionedCacheKey).'.pdf';

        if ($disk->exists($cacheFile)) {
            return response()->file($disk->path($cacheFile), $this->pdfHeaders($filename, $download));
        }

        $pdf = $this->renderPdf($document);
        $disk->put($cacheFile, $pdf);

        return response($pdf, 200, $this->pdfHeaders($filename, $download));
    }

    private function pdfHeaders(string $filename, bool $download): array
    {
        return [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
    }

    public function normalizeBatchExportLimit(mixed $value): int
    {
        $limit = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($limit === false) {
            return self::DEFAULT_BATCH_EXPORT_LIMIT;
        }

        return min($limit, self::MAX_BATCH_EXPORT_LIMIT);
    }

    private function renderPdf(array $document): string
    {
        $this->extendPdfRenderTimeout();

        $html = view('competition.id-cards.pdf', [
            'document' => $document,
        ])->render();

        return Pdf::setOption([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 96,
        ])
            ->loadHTML($html)
            ->setPaper($this->pagePaperSize($document), 'portrait')
            ->output();
    }

    private function extendPdfRenderTimeout(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(self::PDF_RENDER_TIMEOUT_SECONDS);
        }

        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', (string) self::PDF_RENDER_TIMEOUT_SECONDS);
        }
    }

    private function pagePaperSize(array $document): array
    {
        $widthMm = (float) ($document['pageSize']['widthMm'] ?? config('id-cards.page.width_mm'));
        $heightMm = (float) ($document['pageSize']['heightMm'] ?? config('id-cards.page.height_mm'));

        return [0, 0, $this->mmToPoints($widthMm), $this->mmToPoints($heightMm)];
    }

    private function mmToPoints(float $millimeters): float
    {
        return $millimeters * 72 / 25.4;
    }

    private function buildDocument(
        string $subjectType,
        string $title,
        Club $club,
        AgeGroup $ageGroup,
        iterable $records,
        callable $cardBuilder,
    ): array {
        $items = collect($records)->values();

        return [
            'title' => $title,
            'subjectType' => $subjectType,
            'competitionName' => config('id-cards.competition_name'),
            'website' => config('id-cards.website'),
            'organizer' => config('id-cards.organizer'),
            'club' => [
                'name' => $club->name,
                'shortName' => $club->short_name,
                'zone' => $club->zone,
                'logoSrc' => $this->clubLogoSource($club),
                'initials' => $this->initials($club->short_name ?: $club->name),
            ],
            'ageGroup' => [
                'id' => $ageGroup->id,
                'name' => $ageGroup->name,
                'code' => $ageGroup->code,
            ],
            'cardSize' => [
                'widthMm' => (float) config('id-cards.card.width_mm'),
                'heightMm' => (float) config('id-cards.card.height_mm'),
            ],
            'pageSize' => [
                'widthMm' => (float) config('id-cards.page.width_mm'),
                'heightMm' => (float) config('id-cards.page.height_mm'),
            ],
            'cards' => $items->map($cardBuilder)->all(),
            'count' => $items->count(),
            'assets' => [
                'leagueLogoLight' => $this->imageSource(null, config('id-cards.assets.league_logo_light'), self::EMBED_PROFILE_BRAND_LOGO),
                'leagueLogoDark' => $this->imageSource(null, config('id-cards.assets.league_logo_dark'), self::EMBED_PROFILE_BRAND_LOGO),
                'leagueWatermark' => $this->imageSource(null, config('id-cards.assets.league_watermark'), self::EMBED_PROFILE_WATERMARK),
            ],
        ];
    }

    private function buildOfficialCard(Official $official, Club $club, AgeGroup $ageGroup): array
    {
        $registration = $official->registrationForAgeGroup($ageGroup->id);
        $role = $registration?->role ?: $official->role ?: 'Official';
        $license = $registration?->license_levels ?: $official->license_levels ?: $official->license_number ?: '-';
        $identifier = $official->identity_number ?: $official->license_number ?: 'OFF-'.str_pad((string) $official->id, 4, '0', STR_PAD_LEFT);
        $qrPayload = $this->absoluteRoute('public.officials.scan', ['officialSlug' => $official->public_slug]);

        return [
            'id' => 'official-'.$official->id.'-'.$ageGroup->id,
            'type' => 'official',
            'front' => [
                'eyebrow' => 'Competition Accreditation',
                'title' => 'Official Card',
                'badge' => $ageGroup->name,
                'name' => $official->name,
                'role' => $role,
                'club' => $club->name,
                'clubLine' => trim(($club->short_name ?: $club->name).($club->zone ? ' · '.$club->zone : '')),
                'identifierLabel' => 'ID Official',
                'identifierValue' => $identifier,
                'secondaryLabel' => 'License',
                'secondaryValue' => $license,
                'rows' => $this->compactRows([
                    ['label' => 'Nama', 'value' => $official->name],
                    ['label' => 'Peran', 'value' => $role],
                    ['label' => 'Klub', 'value' => $club->name],
                    ['label' => 'TTL', 'value' => $this->birthText($official->birth_place, $official->birth_date?->format('d M Y'))],
                    ['label' => 'Lisensi', 'value' => $license],
                    ['label' => 'ID', 'value' => $identifier],
                ]),
                'meta' => [
                    ['label' => 'TTL', 'value' => $this->birthText($official->birth_place, $official->birth_date?->format('d M Y'))],
                    ['label' => 'Affiliation', 'value' => $club->short_name ?: $club->name],
                ],
                'verificationText' => $official->verification_status === Official::STATUS_APPROVED ? 'OFFICIAL VERIFIED' : null,
                'photoSrc' => $this->personPhotoSource($official->photo_path, $official->name, 'Official'),
                'photoMissing' => blank($official->photo_path),
            ],
            'back' => [
                'title' => 'Validation & Access',
                'subtitle' => 'Scan QR to verify competition data.',
                'facts' => [
                    ['label' => 'Competition', 'value' => config('id-cards.competition_name')],
                    ['label' => 'Club', 'value' => $club->name],
                    ['label' => 'Age Group', 'value' => $ageGroup->name],
                    ['label' => 'Phone', 'value' => $official->phone ?: '-'],
                    ['label' => 'Status', 'value' => $official->is_active ? 'Active' : 'Inactive'],
                ],
                'detailLines' => [
                    'Role: '.$role,
                    'License: '.$license,
                    'Email: '.($official->email ?: '-'),
                ],
                'disclaimer' => 'This card remains the property of the organizer and must be presented during official competition activities.',
                'qrLabel' => 'Official verification',
                'qrSrc' => $this->qrSource($qrPayload),
                'verificationUrl' => $qrPayload,
            ],
        ];
    }

    private function buildPlayerCard(Player $player, Club $club, AgeGroup $ageGroup): array
    {
        $registration = $player->registrationForAgeGroup($ageGroup->id);
        $position = $player->displayPosition($ageGroup->id) ?: 'Player';
        $jersey = $player->displayJerseyNumber($ageGroup->id);
        $identifier = 'PLY-'.str_pad((string) $player->id, 4, '0', STR_PAD_LEFT);
        $qrPayload = $this->absoluteRoute('public.players.scan', ['playerSlug' => $player->public_slug]);

        return [
            'id' => 'player-'.$player->id.'-'.$ageGroup->id,
            'type' => 'player',
            'front' => [
                'eyebrow' => 'Player Registration',
                'title' => 'Player Card',
                'badge' => $ageGroup->name,
                'name' => $player->name,
                'role' => $position,
                'club' => $club->name,
                'clubLine' => trim(($club->short_name ?: $club->name).($club->zone ? ' · '.$club->zone : '')),
                'identifierLabel' => 'ID Pemain',
                'identifierValue' => $identifier,
                'secondaryLabel' => 'Jersey',
                'secondaryValue' => $jersey ? '#'.$jersey : '-',
                'rows' => $this->compactRows([
                    ['label' => 'Nama', 'value' => $player->name],
                    ['label' => 'TTL', 'value' => $this->birthText($player->birth_place, $player->birth_date?->format('d M Y'))],
                    ['label' => 'KU', 'value' => $ageGroup->name],
                    ['label' => 'NP', 'value' => $jersey ? (string) $jersey : '-'],
                    ['label' => 'Klub', 'value' => $club->name],
                ]),
                'meta' => [
                    ['label' => 'TTL', 'value' => $this->birthText($player->birth_place, $player->birth_date?->format('d M Y'))],
                    ['label' => 'KU', 'value' => $ageGroup->name],
                    ['label' => 'No. Pgg', 'value' => $jersey ? '#'.$jersey : '-'],
                    ['label' => 'Posisi', 'value' => $position],
                    ['label' => 'School', 'value' => $player->school_name ?: '-'],
                ],
                'verificationText' => $player->verification_status === Player::STATUS_APPROVED ? 'PLAYER VERIFIED' : null,
                'photoSrc' => $this->personPhotoSource($player->photo_path, $player->name, 'Player'),
                'photoMissing' => blank($player->photo_path),
            ],
            'back' => [
                'title' => 'Player Validation',
                'subtitle' => 'Official matchday and registration credential.',
                'facts' => [
                    ['label' => 'Competition', 'value' => config('id-cards.competition_name')],
                    ['label' => 'Club', 'value' => $club->name],
                    ['label' => 'Age Group', 'value' => $ageGroup->name],
                    ['label' => 'Position', 'value' => $position],
                    ['label' => 'Jersey', 'value' => $jersey ? '#'.$jersey : '-'],
                ],
                'detailLines' => [
                    'ID Pemain: '.$identifier,
                    'Birth: '.$this->birthText($player->birth_place, $player->birth_date?->format('d M Y')),
                    'School: '.($player->school_name ?: '-'),
                ],
                'disclaimer' => 'Only valid for the registered player and the specified age group. Misuse may result in access revocation.',
                'qrLabel' => 'Player verification',
                'qrSrc' => $this->qrSource($qrPayload),
                'verificationUrl' => $qrPayload,
            ],
        ];
    }

    private function qrSource(string $payload): string
    {
        $cacheKey = sha1($payload);

        if (isset($this->qrSourceCache[$cacheKey])) {
            return $this->qrSourceCache[$cacheKey];
        }

        try {
            $renderer = new GDLibRenderer(size: self::QR_IMAGE_SIZE, margin: 2, imageFormat: 'png');
            $writer = new Writer($renderer);

            return $this->qrSourceCache[$cacheKey] = 'data:image/png;base64,'.base64_encode($writer->writeString($payload));
        } catch (\Throwable) {
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320">
  <rect width="320" height="320" fill="#ffffff"/>
  <rect x="12" y="12" width="296" height="296" fill="none" stroke="#1f2937" stroke-width="8"/>
  <text x="160" y="168" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="#1f2937">QR ERROR</text>
</svg>
SVG;

            return $this->qrSourceCache[$cacheKey] = 'data:image/svg+xml;base64,'.base64_encode($svg);
        }
    }

    private function absoluteRoute(string $name, mixed $parameters): string
    {
        $root = rtrim((string) config('app.url'), '/');
        $path = URL::route($name, $parameters, false);

        return $root.$path;
    }

    private function imageSource(?string $path, ?string $fallbackPath = null, ?array $profile = null): ?string
    {
        $cacheKey = sha1(json_encode([$path, $fallbackPath, $profile]));

        if (array_key_exists($cacheKey, $this->imageSourceCache)) {
            return $this->imageSourceCache[$cacheKey];
        }

        if ($path) {
            $source = $this->resolveImageSource($path, $profile);

            if ($source) {
                return $this->imageSourceCache[$cacheKey] = $source;
            }
        }

        if ($fallbackPath && is_file($fallbackPath)) {
            return $this->imageSourceCache[$cacheKey] = $this->fileToDataUri($fallbackPath, $profile);
        }

        return $this->imageSourceCache[$cacheKey] = null;
    }

    private function clubLogoSource(Club $club): string
    {
        return $this->imageSource($club->logo_url, config('id-cards.assets.club_fallback'), self::EMBED_PROFILE_BRAND_LOGO)
            ?? $this->placeholderLogoSvg($this->initials($club->short_name ?: $club->name), '#0d2f57', '#1e5aa5');
    }

    private function personPhotoSource(?string $path, string $name, string $label): string
    {
        return $this->imageSource($path, null, self::EMBED_PROFILE_PERSON_PHOTO)
            ?? $this->placeholderPhotoSvg($this->initials($name), $label);
    }

    private function resolveImageSource(string $path, ?array $profile = null): ?string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $content = @file_get_contents($path);

            if ($content !== false) {
                return $this->binaryToDataUri($content, $path, $profile);
            }

            return $path;
        }

        $candidate = ltrim($path, '/');

        $paths = [
            storage_path('app/public/'.$candidate),
            public_path('storage/'.$candidate),
            public_path($candidate),
        ];

        foreach ($paths as $localPath) {
            if (is_file($localPath)) {
                return $this->fileToDataUri($localPath, $profile);
            }
        }

        return null;
    }

    private function fileToDataUri(string $path, ?array $profile = null): string
    {
        return $this->binaryToDataUri((string) file_get_contents($path), $path, $profile);
    }

    private function binaryToDataUri(string $binary, string $path, ?array $profile = null): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary) ?: (mime_content_type($path) ?: 'image/png');

        if (str_contains((string) $mime, 'image/svg') && class_exists(\Imagick::class)) {
            try {
                $imagick = new \Imagick();
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImageBlob($binary);
                $imagick->setImageFormat('png32');
                $binary = $imagick->getImagesBlob();
                $imagick->clear();
                $imagick->destroy();
                $mime = 'image/png';
            } catch (\Throwable) {
            }
        }

        if ($profile !== null) {
            [$binary, $mime] = $this->optimizeEmbeddedImageBinary($binary, (string) $mime, $profile);
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function optimizeEmbeddedImageBinary(string $binary, string $mime, array $profile): array
    {
        if (! str_starts_with($mime, 'image/')) {
            return [$binary, $mime];
        }

        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            return [$binary, $mime];
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $maxWidth = max(1, (int) ($profile['max_width'] ?? $sourceWidth));
        $maxHeight = max(1, (int) ($profile['max_height'] ?? $sourceHeight));
        $scale = min($maxWidth / max(1, $sourceWidth), $maxHeight / max(1, $sourceHeight), 1);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $format = strtolower((string) ($profile['format'] ?? ''));

        if ($format === '') {
            $format = $mime === 'image/jpeg' ? 'jpeg' : 'png';
        }

        $formatMatchesSource = ($format === 'jpeg' && $mime === 'image/jpeg')
            || ($format === 'png' && $mime === 'image/png');

        if ($targetWidth === $sourceWidth && $targetHeight === $sourceHeight && $formatMatchesSource) {
            imagedestroy($source);

            return [$binary, $mime];
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($source);

            return [$binary, $mime];
        }

        if ($format === 'jpeg') {
            $background = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $background);
        } else {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
            imagealphablending($canvas, true);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();

        if ($format === 'jpeg') {
            imagejpeg($canvas, null, (int) ($profile['quality'] ?? 82));
            $optimizedMime = 'image/jpeg';
        } else {
            imagepng($canvas, null, 6);
            $optimizedMime = 'image/png';
        }

        $optimizedBinary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($optimizedBinary === false || $optimizedBinary === '') {
            return [$binary, $mime];
        }

        return [$optimizedBinary, $optimizedMime];
    }

    private function initials(string $value): string
    {
        $parts = preg_split('/\s+/', trim($value)) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'ID';
    }

    private function placeholderPhotoSvg(string $initials, string $label): string
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="420" viewBox="0 0 320 420">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#f4f8fc"/>
      <stop offset="100%" stop-color="#dde7f2"/>
    </linearGradient>
  </defs>
  <rect width="320" height="420" rx="24" fill="url(#bg)"/>
  <circle cx="160" cy="142" r="58" fill="#c9d8ea"/>
  <path d="M80 324c14-56 54-86 80-86s66 30 80 86" fill="#c9d8ea"/>
  <circle cx="160" cy="142" r="44" fill="#edf3f9"/>
  <path d="M102 324c11-44 38-66 58-66s47 22 58 66" fill="#edf3f9"/>
  <text x="160" y="376" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="700" fill="#0d2f57">{$initials}</text>
  <text x="160" y="404" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="700" letter-spacing="2" fill="#5f7187">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function placeholderLogoSvg(string $initials, string $primary, string $secondary): string
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 220 220">
  <defs>
    <linearGradient id="shield" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$primary}"/>
      <stop offset="100%" stop-color="{$secondary}"/>
    </linearGradient>
  </defs>
  <circle cx="110" cy="110" r="100" fill="#ffffff"/>
  <circle cx="110" cy="110" r="94" fill="url(#shield)"/>
  <circle cx="110" cy="110" r="70" fill="#ffffff" fill-opacity="0.12"/>
  <text x="110" y="124" text-anchor="middle" font-family="Arial, sans-serif" font-size="68" font-weight="800" fill="#ffffff">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function birthText(?string $place, ?string $date): string
    {
        $text = collect([$place, $date])
            ->filter(fn (?string $value) => filled($value))
            ->implode(', ');

        return $text !== '' ? $text : '-';
    }

    private function compactRows(array $rows): array
    {
        return collect($rows)
            ->map(function (array $row): array {
                $label = (string) ($row['label'] ?? '');
                $value = preg_replace('/\s+/', ' ', trim((string) ($row['value'] ?? '-'))) ?: '-';
                $multiline = false;
                $lines = null;

                if ($label === 'TTL') {
                    [$place, $date] = array_pad(array_map('trim', explode(',', $value, 2)), 2, '');

                    if ($date !== '') {
                        $lines = [
                            Str::limit(trim($place), 18, '...'),
                            Str::limit(trim($date), 16, '...'),
                        ];
                        $value = implode(' ', $lines);
                        $multiline = true;
                    } else {
                        $value = Str::limit($value, 28, '...');
                    }
                }

                return [
                    'label' => $label,
                    'value' => $multiline ? $value : Str::limit($value, 28, '...'),
                    'multiline' => $multiline,
                    'lines' => $lines,
                ];
            })
            ->all();
    }
}
