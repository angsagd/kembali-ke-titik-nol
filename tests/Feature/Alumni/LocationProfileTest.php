<?php

use Illuminate\Support\Facades\Schema;

test('location snapshots are stored without local country and city masters', function () {
    expect(Schema::hasTable('countries'))->toBeFalse()
        ->and(Schema::hasTable('cities'))->toBeFalse()
        ->and(Schema::hasColumns('alumni', ['city', 'country', 'latitude', 'longitude']))->toBeTrue()
        ->and(Schema::hasColumns('alumni_timelines', ['city', 'country', 'latitude', 'longitude']))->toBeTrue();
});
