<?php

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('bulk deletes selected match schedules that are not used by lineups', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $ageGroup = AgeGroup::query()->create([
        'name' => 'U-12',
        'code' => 'U12-BULK-1',
        'min_age' => 10,
        'max_age' => 12,
        'is_active' => true,
    ]);

    $clubA = Club::query()->create(['name' => 'Club A']);
    $clubB = Club::query()->create(['name' => 'Club B']);
    $clubC = Club::query()->create(['name' => 'Club C']);

    $firstMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubB->id,
        'match_day' => 'Pekan 1',
        'venue' => 'Lapangan A',
        'match_date' => now()->addDay()->toDateString(),
        'kickoff_time' => '15:00',
    ]);

    $secondMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubC->id,
        'match_day' => 'Pekan 2',
        'venue' => 'Lapangan B',
        'match_date' => now()->addDays(2)->toDateString(),
        'kickoff_time' => '16:00',
    ]);

    $this->actingAs($user)
        ->post(route('matches.bulk-delete'), [
            'selected_ids' => [$firstMatch->id, $secondMatch->id],
        ])
        ->assertRedirect(route('matches.index'))
        ->assertSessionHas('status', '2 jadwal pertandingan berhasil dihapus.');

    $this->assertDatabaseMissing('match_schedules', ['id' => $firstMatch->id]);
    $this->assertDatabaseMissing('match_schedules', ['id' => $secondMatch->id]);
});

it('skips match schedules that are already used by lineups during bulk delete', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $ageGroup = AgeGroup::query()->create([
        'name' => 'U-12',
        'code' => 'U12-BULK-2',
        'min_age' => 10,
        'max_age' => 12,
        'is_active' => true,
    ]);

    $clubA = Club::query()->create(['name' => 'Club A']);
    $clubB = Club::query()->create(['name' => 'Club B']);
    $clubC = Club::query()->create(['name' => 'Club C']);

    $protectedMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubB->id,
        'match_day' => 'Pekan 1',
        'venue' => 'Lapangan A',
        'match_date' => now()->addDay()->toDateString(),
        'kickoff_time' => '15:00',
    ]);

    $deletableMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubC->id,
        'match_day' => 'Pekan 2',
        'venue' => 'Lapangan B',
        'match_date' => now()->addDays(2)->toDateString(),
        'kickoff_time' => '16:00',
    ]);

    LineupList::query()->create([
        'club_id' => $clubA->id,
        'age_group_id' => $ageGroup->id,
        'match_id' => $protectedMatch->id,
        'title' => 'DSP Pekan 1',
        'verification_status' => LineupList::STATUS_DRAFT,
    ]);

    $this->actingAs($user)
        ->post(route('matches.bulk-delete'), [
            'selected_ids' => [$protectedMatch->id, $deletableMatch->id],
        ])
        ->assertRedirect(route('matches.index'))
        ->assertSessionHas('status', '1 jadwal pertandingan berhasil dihapus. 1 jadwal dilewati karena sudah dipakai DSP.');

    $this->assertDatabaseHas('match_schedules', ['id' => $protectedMatch->id]);
    $this->assertDatabaseMissing('match_schedules', ['id' => $deletableMatch->id]);
});
