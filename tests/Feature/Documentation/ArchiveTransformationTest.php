<?php

use App\Models\Alumni;
use App\Models\DocumentationCategory;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DocumentationCategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('documentation categories seed idempotently and can be managed without hard deletion', function () {
    $this->seed(DocumentationCategorySeeder::class);
    $this->seed(DocumentationCategorySeeder::class);

    expect(DocumentationCategory::query()->count())->toBe(8)
        ->and(DocumentationCategory::query()->where('name', 'Kegiatan Lapangan')->exists())->toBeTrue();

    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation-categories.index')
        ->set('name', 'Studio Kartografi')
        ->set('sortOrder', 9)
        ->call('save')
        ->assertHasNoErrors();

    $category = DocumentationCategory::query()->where('slug', 'studio-kartografi')->firstOrFail();

    Livewire::test('pages::admin.documentation-categories.index')
        ->call('edit', $category->id)
        ->set('name', 'Studio Pemetaan')
        ->set('sortOrder', 10)
        ->call('save')
        ->assertHasNoErrors();

    $category->refresh();
    expect($category->name)->toBe('Studio Pemetaan')
        ->and($category->slug)->toBe('studio-pemetaan')
        ->and($category->sort_order)->toBe(10);

    Livewire::test('pages::admin.documentation-categories.index')
        ->call('toggleActive', $category->id);

    expect($category->fresh()->is_active)->toBeFalse();
});

test('archive filters by year category and type using historical date order', function () {
    $viewer = Alumni::factory()->create();
    $kuliah = DocumentationCategory::factory()->create(['name' => 'Kuliah', 'slug' => 'kuliah']);
    $reuni = DocumentationCategory::factory()->create(['name' => 'Reuni', 'slug' => 'reuni']);
    MediaItem::factory()->photo()->create(['title' => 'Kuliah 1998', 'year' => 1998, 'month' => 6, 'documentation_category_id' => $kuliah->id]);
    MediaItem::factory()->video()->create(['title' => 'Reuni 2026', 'year' => 2026, 'month' => 8, 'documentation_category_id' => $reuni->id]);
    MediaItem::factory()->photo()->create(['title' => 'Kuliah 2026', 'year' => 2026, 'month' => 1, 'documentation_category_id' => $kuliah->id]);

    $this->actingAs($viewer->user);

    Livewire::test('pages::documentation.index')
        ->assertSeeInOrder(['Reuni 2026', 'Kuliah 2026', 'Kuliah 1998'])
        ->set('archiveYear', 2026)
        ->set('archiveCategoryId', $kuliah->id)
        ->set('archiveType', 'photo')
        ->assertSee('Kuliah 2026')
        ->assertDontSee('Reuni 2026')
        ->assertDontSee('Kuliah 1998');
});

test('new photos are compressed into private storage and delivered according to visibility', function () {
    Storage::fake('local');
    Storage::fake('public');

    $uploader = Alumni::factory()->create();
    $category = DocumentationCategory::factory()->create();
    $this->actingAs($uploader->user);

    Livewire::test('pages::documentation.index')
        ->set('photo', UploadedFile::fake()->image('praktikum.jpg', 2400, 1600)->size(1200))
        ->set('title', 'Praktikum Lapangan')
        ->set('year', 1998)
        ->set('documentation_category_id', $category->id)
        ->set('visibility', 'internal')
        ->call('saveMedia')
        ->assertHasNoErrors();

    $mediaItem = MediaItem::query()->where('title', 'Praktikum Lapangan')->firstOrFail();

    expect($mediaItem->file_path)->toEndWith('.webp')
        ->and($mediaItem->width)->toBe(1920)
        ->and($mediaItem->height)->toBe(1280)
        ->and($mediaItem->documentation_category_id)->toBe($category->id);
    Storage::disk('local')->assertExists($mediaItem->file_path);
    Storage::disk('public')->assertMissing($mediaItem->file_path);

    auth()->logout();
    $this->get(route('media.file', $mediaItem))->assertForbidden();
    $this->actingAs($uploader->user)->get(route('media.file', $mediaItem))->assertOk()->assertHeader('Cache-Control', 'no-store, private');

    $mediaItem->update(['visibility' => 'public']);
    auth()->logout();
    $this->get(route('media.file', $mediaItem))->assertOk();
});

test('legacy internal media command moves public files into private storage', function () {
    Storage::fake('local');
    Storage::fake('public');
    $mediaItem = MediaItem::factory()->photo()->create(['visibility' => 'internal', 'file_path' => 'documentation/photos/legacy.jpg']);
    Storage::disk('public')->put($mediaItem->file_path, 'legacy-photo');

    Artisan::call('media:secure-internal');

    Storage::disk('local')->assertExists($mediaItem->file_path);
    Storage::disk('public')->assertMissing($mediaItem->file_path);
});
