<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\MediaItem;
use App\Models\News;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $latestNewsItems = News::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $publicMediaItems = MediaItem::query()
            ->where('visibility', 'public')
            ->latest()
            ->limit(3)
            ->get();

        $publicDonations = Donation::query()
            ->with('alumni:id,full_name')
            ->where('publication_status', 'show_name')
            ->latest()
            ->limit(16)
            ->get();

        $anonymousDonorCount = Donation::query()
            ->where('publication_status', 'anonymous')
            ->count();

        return view('welcome', compact(
            'anonymousDonorCount',
            'latestNewsItems',
            'publicDonations',
            'publicMediaItems',
        ));
    }
}
