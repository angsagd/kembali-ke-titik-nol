<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MediaItem $mediaItem): BinaryFileResponse
    {
        abort_unless($mediaItem->isPhoto() && filled($mediaItem->file_path), 404);
        abort_unless(
            $mediaItem->visibility === 'public'
                || (Auth::check() && Auth::user()->can('view-alumni-directory')),
            403,
        );

        $disk = Storage::disk('local')->exists($mediaItem->file_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($mediaItem->file_path), 404);

        $response = response()->file(Storage::disk($disk)->path($mediaItem->file_path), [
            'Content-Type' => Storage::disk($disk)->mimeType($mediaItem->file_path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        if ($mediaItem->visibility === 'public') {
            $response->setPublic()->setMaxAge(86400);
        } else {
            $response->setPrivate();
            $response->headers->addCacheControlDirective('no-store');
        }

        return $response;
    }
}
