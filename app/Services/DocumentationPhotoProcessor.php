<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentationPhotoProcessor
{
    /** @return array{path: string, file_size: int, width: int, height: int} */
    public function store(UploadedFile $photo): array
    {
        $source = imagecreatefromstring($photo->getContent());

        if ($source === false) {
            throw new RuntimeException('Dokumentasi foto tidak dapat diproses.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 1920 / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $processed = imagecreatetruecolor($width, $height);

        imagealphablending($processed, false);
        imagesavealpha($processed, true);
        imagecopyresampled($processed, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        ob_start();
        $encoded = imagewebp($processed, null, 82);
        $contents = ob_get_clean();
        imagedestroy($processed);
        imagedestroy($source);

        if (! $encoded || ! is_string($contents)) {
            throw new RuntimeException('Dokumentasi foto tidak dapat dikompresi.');
        }

        $path = 'documentation/photos/'.Str::uuid().'.webp';
        Storage::disk('local')->put($path, $contents);

        return ['path' => $path, 'file_size' => strlen($contents), 'width' => $width, 'height' => $height];
    }
}
