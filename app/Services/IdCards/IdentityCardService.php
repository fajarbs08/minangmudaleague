<?php

namespace App\Services\IdCards;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IdentityCardService
{
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

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
        ]);
    }

    public function pdfResponseCached(array $document, string $filename, string $cacheKey, bool $download = false): Response
    {
        $disk = Storage::disk('local');
        $cacheDir = 'id-cards-cache';
        $cacheFile = $cacheDir.'/'.sha1($cacheKey).'.pdf';

        if ($disk->exists($cacheFile)) {
            return response()->file($disk->path($cacheFile), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
            ]);
        }

        $pdf = $this->renderPdf($document);
        $disk->put($cacheFile, $pdf);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
        ]);
    }

    private function renderPdf(array $document): string
    {
        $html = view('competition.id-cards.print', [
            'document' => $document,
        ])->render();

        $chromePath = $this->detectExecutable([
            (string) config('id-cards.chrome_path'),
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium-browser',
            '/usr/local/bin/chromium',
            'google-chrome',
            'chromium-browser',
            'chromium',
        ]);

        $nodeBinary = $this->detectExecutable([
            (string) config('id-cards.node_binary'),
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
            'node',
        ]);

        if ($chromePath === null || $nodeBinary === null) {
            return $this->renderPdfWithDomPdf($html);
        }

        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->emulateMedia('screen')
            ->margins(0, 0, 0, 0)
            ->paperSize(
                (float) config('id-cards.page.width_mm'),
                (float) config('id-cards.page.height_mm'),
                'mm'
            )
            ->setOption('preferCSSPageSize', true)
            ->setOption('printBackground', true)
            ->setOption('displayHeaderFooter', false)
            ->setOption('waitUntil', (string) config('id-cards.wait_until', 'load'))
            ->timeout((int) config('id-cards.timeout'))
            ->addChromiumArguments([
                'disable-dev-shm-usage',
                'font-render-hinting=medium',
            ]);

        $browsershot->setChromePath($chromePath);
        $browsershot->setNodeBinary($nodeBinary);

        $nodeModulesPath = (string) config('id-cards.node_modules_path');
        if ($nodeModulesPath !== '' && is_dir($nodeModulesPath)) {
            $browsershot->setNodeModulePath($nodeModulesPath);
        }

        if (config('id-cards.no_sandbox')) {
            $browsershot->noSandbox();
        }

        try {
            return $browsershot->pdf();
        } catch (Throwable) {
            return $this->renderPdfWithDomPdf($html);
        }
    }

    private function renderPdfWithDomPdf(string $html): string
    {
        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    private function detectExecutable(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            if (str_contains($candidate, DIRECTORY_SEPARATOR)) {
                if (is_executable($candidate)) {
                    return $candidate;
                }

                continue;
            }

            $resolved = trim((string) shell_exec('command -v '.escapeshellarg($candidate).' 2>/dev/null'));
            if ($resolved !== '' && is_executable($resolved)) {
                return $resolved;
            }
        }

        return null;
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
                'leagueLogoLight' => $this->imageSource(null, config('id-cards.assets.league_logo_light')),
                'leagueLogoDark' => $this->imageSource(null, config('id-cards.assets.league_logo_dark')),
                'leagueWatermark' => $this->imageSource(null, config('id-cards.assets.league_watermark')),
            ],
        ];
    }

    private function buildOfficialCard(Official $official, Club $club, AgeGroup $ageGroup): array
    {
        $registration = $official->registrationForAgeGroup($ageGroup->id);
        $role = $registration?->role ?: $official->role ?: 'Official';
        $license = $registration?->license_levels ?: $official->license_levels ?: $official->license_number ?: '-';
        $identifier = $official->identity_number ?: $official->license_number ?: 'OFF-'.str_pad((string) $official->id, 4, '0', STR_PAD_LEFT);
        $qrPayload = $this->absoluteRoute('officials.scan-result', $official);

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
                'rows' => [
                    ['label' => 'Nama', 'value' => $official->name],
                    ['label' => 'Peran', 'value' => $role],
                    ['label' => 'Klub', 'value' => $club->name],
                    ['label' => 'TTL', 'value' => $this->birthText($official->birth_place, $official->birth_date?->format('d M Y'))],
                    ['label' => 'Lisensi', 'value' => $license],
                    ['label' => 'ID', 'value' => $identifier],
                ],
                'meta' => [
                    ['label' => 'TTL', 'value' => $this->birthText($official->birth_place, $official->birth_date?->format('d M Y'))],
                    ['label' => 'Affiliation', 'value' => $club->short_name ?: $club->name],
                ],
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
        $qrPayload = $this->absoluteRoute('players.scan-result', $player);

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
                'rows' => [
                    ['label' => 'Nama', 'value' => $player->name],
                    ['label' => 'TTL', 'value' => $this->birthText($player->birth_place, $player->birth_date?->format('d M Y'))],
                    ['label' => 'KU', 'value' => $ageGroup->name],
                    ['label' => 'NP', 'value' => $jersey ? (string) $jersey : '-'],
                    ['label' => 'Klub', 'value' => $club->name],
                ],
                'meta' => [
                    ['label' => 'TTL', 'value' => $this->birthText($player->birth_place, $player->birth_date?->format('d M Y'))],
                    ['label' => 'KU', 'value' => $ageGroup->name],
                    ['label' => 'No. Pgg', 'value' => $jersey ? '#'.$jersey : '-'],
                    ['label' => 'Posisi', 'value' => $position],
                    ['label' => 'School', 'value' => $player->school_name ?: '-'],
                ],
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
        return 'data:image/svg+xml;base64,'.base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(320)
                ->margin(1)
                ->generate($payload)
        );
    }

    private function absoluteRoute(string $name, mixed $parameters): string
    {
        $root = rtrim((string) config('app.url'), '/');
        $path = URL::route($name, $parameters, false);

        return $root.$path;
    }

    private function imageSource(?string $path, ?string $fallbackPath = null): ?string
    {
        if ($path) {
            $source = $this->resolveImageSource($path);

            if ($source) {
                return $source;
            }
        }

        if ($fallbackPath && is_file($fallbackPath)) {
            return $this->fileToDataUri($fallbackPath);
        }

        return null;
    }

    private function clubLogoSource(Club $club): string
    {
        return $this->imageSource($club->logo_url, config('id-cards.assets.club_fallback'))
            ?? $this->placeholderLogoSvg($this->initials($club->short_name ?: $club->name), '#0d2f57', '#1e5aa5');
    }

    private function personPhotoSource(?string $path, string $name, string $label): string
    {
        return $this->imageSource($path)
            ?? $this->placeholderPhotoSvg($this->initials($name), $label);
    }

    private function resolveImageSource(string $path): ?string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $content = @file_get_contents($path);

            if ($content !== false) {
                return $this->binaryToDataUri($content, $path);
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
                return $this->fileToDataUri($localPath);
            }
        }

        return null;
    }

    private function fileToDataUri(string $path): string
    {
        return $this->binaryToDataUri((string) file_get_contents($path), $path);
    }

    private function binaryToDataUri(string $binary, string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary) ?: (mime_content_type($path) ?: 'image/png');

        return 'data:'.$mime.';base64,'.base64_encode($binary);
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
}
