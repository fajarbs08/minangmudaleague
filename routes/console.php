<?php

use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use App\Services\ClubLogoService;
use App\Services\ImageAssetService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('clubs:normalize-logos {--club_id=* : Batasi ke ID klub tertentu} {--force : Proses ulang logo yang sudah dinormalisasi}', function (ClubLogoService $clubLogoService) {
    $clubIds = collect((array) $this->option('club_id'))
        ->filter(fn ($value) => filled($value))
        ->map(fn ($value) => (int) $value)
        ->filter(fn (int $value) => $value > 0)
        ->values();
    $force = (bool) $this->option('force');

    $clubs = Club::query()
        ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $clubIds))
        ->whereNotNull('logo_url')
        ->orderBy('id')
        ->get(['id', 'name', 'logo_url']);

    if ($clubs->isEmpty()) {
        $this->warn('Tidak ada logo klub yang dapat diproses.');

        return self::SUCCESS;
    }

    $processed = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($clubs as $club) {
        $currentPath = (string) $club->logo_url;

        if (str_starts_with($currentPath, 'http://') || str_starts_with($currentPath, 'https://')) {
            $this->line("Skip klub #{$club->id} {$club->name}: logo eksternal.");
            $skipped++;

            continue;
        }

        if (! $force && $clubLogoService->isNormalizedPath($currentPath)) {
            $this->line("Skip klub #{$club->id} {$club->name}: logo sudah standar.");
            $skipped++;

            continue;
        }

        try {
            $newPath = $clubLogoService->normalizeStoredPath($currentPath);

            $club->forceFill([
                'logo_url' => $newPath,
            ])->save();

            $oldPath = ltrim($currentPath, '/');

            if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $this->info("OK klub #{$club->id} {$club->name}: {$newPath}");
            $processed++;
        } catch (Throwable $exception) {
            $this->error("Gagal klub #{$club->id} {$club->name}: {$exception->getMessage()}");
            $failed++;
        }
    }

    $this->newLine();
    $this->table(
        ['Diproses', 'Skip', 'Gagal'],
        [[$processed, $skipped, $failed]]
    );

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Normalisasi ulang seluruh logo klub ke format standar');

Artisan::command('media:normalize-images {--force : Proses ulang file yang sudah dinormalisasi} {--club_id=* : Batasi ke ID klub tertentu untuk data klub, pemain, dan official}', function (ImageAssetService $imageAssetService) {
    $force = (bool) $this->option('force');
    $clubIds = collect((array) $this->option('club_id'))
        ->filter(fn ($value) => filled($value))
        ->map(fn ($value) => (int) $value)
        ->filter(fn (int $value) => $value > 0)
        ->values();

    $targets = [
        [
            'label' => 'Logo klub',
            'items' => Club::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $clubIds))
                ->whereNotNull('logo_url')
                ->get(['id', 'name', 'logo_url']),
            'field' => 'logo_url',
            'directory' => 'club-logos',
            'profile' => ['mode' => 'square', 'canvas_size' => 512, 'padding' => 48],
        ],
        [
            'label' => 'Surat klub',
            'items' => Club::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $clubIds))
                ->whereNotNull('statement_file_path')
                ->get(['id', 'name', 'statement_file_path']),
            'field' => 'statement_file_path',
            'directory' => 'clubs/statements',
            'profile' => ['mode' => 'contain', 'max_width' => 2800, 'max_height' => 2800],
        ],
        [
            'label' => 'Foto pemain',
            'items' => Player::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('club_id', $clubIds))
                ->whereNotNull('photo_path')
                ->get(['id', 'name', 'photo_path']),
            'field' => 'photo_path',
            'directory' => 'players/photos',
            'profile' => ['mode' => 'contain', 'max_width' => 1600, 'max_height' => 1600],
        ],
        [
            'label' => 'Dokumen pemain',
            'items' => Player::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('club_id', $clubIds))
                ->where(function ($query) {
                    $query->whereNotNull('diploma_file_path')
                        ->orWhereNotNull('report_file_path')
                        ->orWhereNotNull('birth_certificate_file_path')
                        ->orWhereNotNull('family_card_file_path');
                })
                ->get(['id', 'name', 'diploma_file_path', 'report_file_path', 'birth_certificate_file_path', 'family_card_file_path']),
            'field' => ['diploma_file_path', 'report_file_path', 'birth_certificate_file_path', 'family_card_file_path'],
            'directory' => [
                'diploma_file_path' => 'players/diplomas',
                'report_file_path' => 'players/reports',
                'birth_certificate_file_path' => 'players/birth-certificates',
                'family_card_file_path' => 'players/family-cards',
            ],
            'profile' => ['mode' => 'contain', 'max_width' => 2800, 'max_height' => 2800],
        ],
        [
            'label' => 'Foto official',
            'items' => Official::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('club_id', $clubIds))
                ->whereNotNull('photo_path')
                ->get(['id', 'name', 'photo_path']),
            'field' => 'photo_path',
            'directory' => 'officials/photos',
            'profile' => ['mode' => 'contain', 'max_width' => 1600, 'max_height' => 1600],
        ],
        [
            'label' => 'Dokumen official',
            'items' => Official::query()
                ->when($clubIds->isNotEmpty(), fn ($query) => $query->whereIn('club_id', $clubIds))
                ->where(function ($query) {
                    $query->whereNotNull('license_file_path')
                        ->orWhereNotNull('identity_file_path');
                })
                ->get(['id', 'name', 'license_file_path', 'identity_file_path']),
            'field' => ['license_file_path', 'identity_file_path'],
            'directory' => [
                'license_file_path' => 'officials/licenses',
                'identity_file_path' => 'officials/identity',
            ],
            'profile' => ['mode' => 'contain', 'max_width' => 2800, 'max_height' => 2800],
        ],
    ];

    $processed = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($targets as $target) {
        foreach ($target['items'] as $item) {
            $fields = is_array($target['field']) ? $target['field'] : [$target['field']];

            foreach ($fields as $field) {
                $currentPath = (string) ($item->{$field} ?? '');

                if ($currentPath === '') {
                    continue;
                }

                if (str_starts_with($currentPath, 'http://') || str_starts_with($currentPath, 'https://')) {
                    $this->line("Skip {$target['label']} #{$item->id}: file eksternal.");
                    $skipped++;

                    continue;
                }

                $directory = is_array($target['directory']) ? $target['directory'][$field] : $target['directory'];

                if (! $force && $imageAssetService->isNormalizedPath($currentPath, $directory)) {
                    $skipped++;

                    continue;
                }

                try {
                    $newPath = $imageAssetService->normalizeStoredPathIfImage($currentPath, $directory, $target['profile']);

                    if ($newPath === null) {
                        $skipped++;

                        continue;
                    }

                    $payload = [$field => $newPath];

                    $item->forceFill($payload)->save();

                    $oldPath = ltrim($currentPath, '/');

                    if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $this->info("OK {$target['label']} #{$item->id}: {$newPath}");
                    $processed++;
                } catch (Throwable $exception) {
                    $this->error("Gagal {$target['label']} #{$item->id}: {$exception->getMessage()}");
                    $failed++;
                }
            }
        }
    }

    $this->newLine();
    $this->table(
        ['Diproses', 'Skip', 'Gagal'],
        [[$processed, $skipped, $failed]]
    );

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Normalisasi ulang seluruh gambar upload ke standar per kategori');
