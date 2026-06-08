<?php

use App\Models\Alumni;
use Illuminate\Support\Facades\Storage;

test('database backup command creates a sqlite sql backup file', function () {
    Storage::fake('local');

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    $this->artisan('backup:database', [
        '--disk' => 'local',
        '--path' => 'backups/database',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Backup database berhasil dibuat');

    $files = Storage::disk('local')->allFiles('backups/database');

    expect($files)->toHaveCount(1);

    $contents = Storage::disk('local')->get($files[0]);

    expect($files[0])->toEndWith('.sql')
        ->and($contents)
        ->toContain('CREATE TABLE')
        ->toContain('Ade Chandra')
        ->toContain('D096001')
        ->toContain('COMMIT;');
});
