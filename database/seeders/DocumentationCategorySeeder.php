<?php

namespace Database\Seeders;

use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Kuliah', 'Praktikum', 'Kegiatan Lapangan', 'Nongkrong', 'Wisuda', 'Pertemuan Alumni', 'Reuni', 'Lainnya'] as $index => $name) {
            DocumentationCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
