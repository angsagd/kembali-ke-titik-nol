<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

#[Signature('media:secure-internal')]
#[Description('Move legacy internal documentation photos from public to private storage')]
class SecureInternalMedia extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moved = 0;

        MediaItem::query()
            ->where('type', 'photo')
            ->where('visibility', 'internal')
            ->whereNotNull('file_path')
            ->chunkById(100, function ($mediaItems) use (&$moved): void {
                foreach ($mediaItems as $mediaItem) {
                    if (! Storage::disk('public')->exists($mediaItem->file_path)) {
                        continue;
                    }

                    $stream = Storage::disk('public')->readStream($mediaItem->file_path);

                    if ($stream === null) {
                        continue;
                    }

                    $stored = Storage::disk('local')->writeStream($mediaItem->file_path, $stream);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if ($stored && Storage::disk('local')->exists($mediaItem->file_path)) {
                        Storage::disk('public')->delete($mediaItem->file_path);
                        $moved++;
                    }
                }
            });

        $this->info("{$moved} internal media file(s) secured.");

        return SymfonyCommand::SUCCESS;
    }
}
