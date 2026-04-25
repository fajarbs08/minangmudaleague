<?php

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\OfficialAgeGroup;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Services\ClubLogoService;
use App\Services\ImageAssetService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
            'disk' => 'public',
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
            'disk' => 'local',
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
            'disk' => 'public',
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
            'disk' => 'local',
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
            'disk' => 'public',
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
            'disk' => 'local',
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
                $disk = $target['disk'] ?? 'public';

                if (! $force && $imageAssetService->isNormalizedPath($currentPath, $directory)) {
                    $skipped++;

                    continue;
                }

                try {
                    $newPath = $imageAssetService->normalizeStoredPathIfImage($currentPath, $directory, $target['profile'], $disk);

                    if ($newPath === null) {
                        $skipped++;

                        continue;
                    }

                    $payload = [$field => $newPath];

                    $item->forceFill($payload)->save();

                    $oldPath = ltrim($currentPath, '/');

                    if ($oldPath !== $newPath) {
                        if ($disk === 'local') {
                            $imageAssetService->deleteDocumentUpload($oldPath);
                        } elseif (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
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

Artisan::command('documents:secure-sensitive-files', function (ImageAssetService $imageAssetService) {
    $targets = [
        [
            'label' => 'Surat klub',
            'items' => Club::query()
                ->whereNotNull('statement_file_path')
                ->get(['id', 'name', 'statement_file_path']),
            'field' => 'statement_file_path',
        ],
        [
            'label' => 'Dokumen pemain',
            'items' => Player::query()
                ->where(function ($query) {
                    $query->whereNotNull('diploma_file_path')
                        ->orWhereNotNull('report_file_path')
                        ->orWhereNotNull('birth_certificate_file_path')
                        ->orWhereNotNull('family_card_file_path');
                })
                ->get(['id', 'name', 'diploma_file_path', 'report_file_path', 'birth_certificate_file_path', 'family_card_file_path']),
            'field' => ['diploma_file_path', 'report_file_path', 'birth_certificate_file_path', 'family_card_file_path'],
        ],
        [
            'label' => 'Dokumen official',
            'items' => Official::query()
                ->where(function ($query) {
                    $query->whereNotNull('license_file_path')
                        ->orWhereNotNull('identity_file_path');
                })
                ->get(['id', 'name', 'license_file_path', 'identity_file_path']),
            'field' => ['license_file_path', 'identity_file_path'],
        ],
    ];

    $secured = 0;
    $missing = 0;
    $failed = 0;

    foreach ($targets as $target) {
        foreach ($target['items'] as $item) {
            foreach ((array) $target['field'] as $field) {
                $path = $item->{$field};

                if (blank($path) || $imageAssetService->isExternalPath($path)) {
                    continue;
                }

                try {
                    if (! $imageAssetService->moveDocumentUploadToPrivateDisk($path)) {
                        $missing++;
                        $this->warn("Lewati {$target['label']} #{$item->id}: file tidak ditemukan.");

                        continue;
                    }

                    $secured++;
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("Gagal {$target['label']} #{$item->id}: {$exception->getMessage()}");
                }
            }
        }
    }

    $this->newLine();
    $this->table(
        ['Diamankan', 'Tidak ditemukan', 'Gagal'],
        [[$secured, $missing, $failed]]
    );

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Pindahkan dokumen sensitif lama dari penyimpanan publik ke private storage');

Artisan::command('matches:autofill-knockout-sources {--age_group_id=* : Batasi ke ID kelompok usia tertentu} {--force : Tulis ulang relasi sumber yang sudah ada} {--dry-run : Tampilkan perubahan tanpa menyimpan}', function () {
    $ageGroupIds = collect((array) $this->option('age_group_id'))
        ->filter(fn ($value) => filled($value))
        ->map(fn ($value) => (int) $value)
        ->filter(fn (int $value) => $value > 0)
        ->values();
    $force = (bool) $this->option('force');
    $dryRun = (bool) $this->option('dry-run');

    $matches = MatchSchedule::query()
        ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
        ->when($ageGroupIds->isNotEmpty(), fn ($query) => $query->whereIn('age_group_id', $ageGroupIds))
        ->orderBy('age_group_id')
        ->orderBy('round_order')
        ->orderBy('bracket_slot')
        ->get([
            'id',
            'age_group_id',
            'round_label',
            'round_order',
            'bracket_slot',
            'source_match_a_id',
            'source_match_b_id',
        ]);

    if ($matches->isEmpty()) {
        $this->warn('Tidak ada pertandingan knockout yang bisa diproses.');

        return self::SUCCESS;
    }

    $updated = 0;
    $skipped = 0;
    $rows = [];

    foreach ($matches->groupBy('age_group_id') as $ageGroupId => $ageGroupMatches) {
        $rounds = $ageGroupMatches
            ->groupBy(fn (MatchSchedule $match) => (int) ($match->round_order ?: 1))
            ->sortKeys();
        $roundOrders = $rounds->keys()->map(fn ($value) => (int) $value)->values();

        foreach ($roundOrders as $index => $roundOrder) {
            if ($index === 0) {
                continue;
            }

            $previousRoundOrder = (int) $roundOrders[$index - 1];
            $previousRoundMatches = $rounds->get($previousRoundOrder, collect())
                ->keyBy(fn (MatchSchedule $match) => (int) ($match->bracket_slot ?: 1));

            foreach ($rounds->get($roundOrder, collect()) as $match) {
                if (! $force && ($match->source_match_a_id || $match->source_match_b_id)) {
                    $skipped++;
                    continue;
                }

                $slot = (int) ($match->bracket_slot ?: 1);
                $expectedSourceSlotA = (($slot - 1) * 2) + 1;
                $expectedSourceSlotB = $expectedSourceSlotA + 1;
                $sourceMatchAId = $previousRoundMatches->get($expectedSourceSlotA)?->id;
                $sourceMatchBId = $previousRoundMatches->get($expectedSourceSlotB)?->id;

                if (! $sourceMatchAId && ! $sourceMatchBId) {
                    $skipped++;
                    continue;
                }

                if ((int) ($match->source_match_a_id ?: 0) === (int) ($sourceMatchAId ?: 0)
                    && (int) ($match->source_match_b_id ?: 0) === (int) ($sourceMatchBId ?: 0)) {
                    $skipped++;
                    continue;
                }

                $rows[] = [
                    $match->id,
                    $ageGroupId,
                    $match->round_label ?: 'Babak '.$roundOrder,
                    $slot,
                    $sourceMatchAId ?: '-',
                    $sourceMatchBId ?: '-',
                ];

                if (! $dryRun) {
                    $match->forceFill([
                        'source_match_a_id' => $sourceMatchAId,
                        'source_match_b_id' => $sourceMatchBId,
                    ])->save();
                }

                $updated++;
            }
        }
    }

    if (! empty($rows)) {
        $this->table(
            ['Match ID', 'Age Group', 'Round', 'Slot', 'Source A', 'Source B'],
            $rows
        );
    }

    $this->newLine();
    $this->table(
        ['Mode', 'Diupdate', 'Skip'],
        [[
            $dryRun ? 'DRY RUN' : 'WRITE',
            $updated,
            $skipped,
        ]]
    );

    if ($dryRun) {
        $this->info('Dry run selesai. Jalankan ulang tanpa --dry-run untuk menyimpan relasi sumber.');
    } else {
        $this->info('Autofill relasi sumber bracket knockout selesai.');
    }

    return self::SUCCESS;
})->purpose('Autofill relasi sumber pertandingan pada bracket knockout berdasarkan pola slot standar');

Artisan::command('age-groups:purge-pdf-duplicates {--dry-run : Tampilkan dampak tanpa menghapus data}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $ageGroups = AgeGroup::query()
        ->where('code', 'like', '%PDF%')
        ->orderBy('id')
        ->get(['id', 'name', 'code', 'is_active']);

    if ($ageGroups->isEmpty()) {
        $this->warn('Tidak ada age group PDF duplicate yang ditemukan.');

        return self::SUCCESS;
    }

    $rows = [];
    $purgeableMatchCount = 0;
    $purgeableIds = [];
    $blocked = 0;

    foreach ($ageGroups as $ageGroup) {
        $matchCount = MatchSchedule::query()->where('age_group_id', $ageGroup->id)->count();
        $lineupCount = LineupList::query()->where('age_group_id', $ageGroup->id)->count();
        $playerPrimaryCount = Player::query()->where('primary_age_group_id', $ageGroup->id)->count();
        $playerAgeCount = PlayerAgeGroup::query()->where('age_group_id', $ageGroup->id)->count();
        $officialAgeCount = OfficialAgeGroup::query()->where('age_group_id', $ageGroup->id)->count();
        $isPurgeable = $lineupCount === 0 && $playerPrimaryCount === 0 && $playerAgeCount === 0 && $officialAgeCount === 0;

        $rows[] = [
            $ageGroup->id,
            $ageGroup->name,
            $ageGroup->code,
            $matchCount,
            $lineupCount,
            $playerPrimaryCount,
            $playerAgeCount,
            $officialAgeCount,
            $isPurgeable ? 'YA' : 'TIDAK',
        ];

        if ($isPurgeable) {
            $purgeableIds[] = $ageGroup->id;
            $purgeableMatchCount += $matchCount;
        } else {
            $blocked++;
        }
    }

    $this->table(
        ['ID', 'Nama', 'Code', 'Matches', 'DSP', 'Players', 'Player Ages', 'Official Ages', 'Purgeable'],
        $rows
    );

    if ($dryRun) {
        $this->newLine();
        $this->info('Dry run selesai. Jalankan ulang tanpa --dry-run untuk menghapus data yang aman.');
        $this->table(['Siap Hapus', 'Diblokir'], [[count($purgeableIds), $blocked]]);

        return self::SUCCESS;
    }

    if (empty($purgeableIds)) {
        $this->warn('Tidak ada age group PDF duplicate yang aman untuk dihapus.');

        return self::SUCCESS;
    }

    DB::transaction(function () use ($purgeableIds) {
        MatchSchedule::query()->whereIn('age_group_id', $purgeableIds)->delete();
        AgeGroup::query()->whereIn('id', $purgeableIds)->delete();
    });

    $this->newLine();
    $this->info('Purge PDF duplicate selesai.');
    $this->table(['Age Groups Dihapus', 'Match Schedules Dihapus', 'Diblokir'], [[
        count($purgeableIds),
        $purgeableMatchCount,
        $blocked,
    ]]);

    return self::SUCCESS;
})->purpose('Hapus age group PDF duplicate yang hanya dipakai data match dan tidak dipakai registrasi utama');
