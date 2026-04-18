<?php

use App\Models\AgeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes only the four competition age groups', function () {
    expect(AgeGroup::competition()->pluck('code')->all())->toBe(['U10', 'U12', 'U14', 'U16']);
});
