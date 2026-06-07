<?php

use App\Http\Controllers\Reports\ExportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('galeri', 'pages::public.gallery')->name('public.gallery');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('alumni/profile', 'pages::alumni.profile')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.profile');

    Route::livewire('alumni/timeline', 'pages::alumni.timeline.index')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.timeline.index');

    Route::livewire('alumni/rsvp', 'pages::alumni.rsvp')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.rsvp');

    Route::livewire('alumni/room', 'pages::alumni.room')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.room');

    Route::livewire('alumni/finance', 'pages::alumni.finance')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.finance');

    Route::livewire('documentation', 'pages::documentation.index')
        ->middleware('can:update-own-alumni-profile')
        ->name('documentation.index');

    Route::livewire('news', 'pages::news.index')->name('news.index');
    Route::livewire('news/{news:slug}', 'pages::news.show')->name('news.show');

    Route::livewire('whatsapp/analytics', 'pages::whatsapp.analytics')
        ->middleware('can:view-whatsapp-analytics')
        ->name('whatsapp.analytics');

    Route::middleware('can:view-alumni-directory')->group(function () {
        Route::livewire('alumni/directory', 'pages::alumni.directory.index')->name('alumni.directory.index');
        Route::livewire('alumni/directory/{alumni}', 'pages::alumni.directory.show')->name('alumni.directory.show');
        Route::livewire('alumni/distribution', 'pages::alumni.distribution.index')->name('alumni.distribution.index');
    });

    Route::middleware('can:manage-alumni')->group(function () {
        Route::livewire('admin/alumni', 'pages::admin.alumni.index')->name('admin.alumni.index');
        Route::livewire('admin/alumni/{alumni}', 'pages::admin.alumni.show')->name('admin.alumni.show');
        Route::livewire('admin/rsvp', 'pages::admin.rsvp.index')->name('admin.rsvp.index');
        Route::livewire('admin/rooming', 'pages::admin.rooming.index')->name('admin.rooming.index');
        Route::livewire('admin/documentation', 'pages::admin.documentation.index')->name('admin.documentation.index');
        Route::livewire('admin/news', 'pages::admin.news.index')->name('admin.news.index');
        Route::livewire('admin/whatsapp', 'pages::admin.whatsapp.index')
            ->middleware('can:import-whatsapp-analytics')
            ->name('admin.whatsapp.index');
        Route::get('reports/rsvp/export', [ExportController::class, 'rsvp'])->name('reports.rsvp.export');
        Route::get('reports/rooming/export', [ExportController::class, 'rooming'])->name('reports.rooming.export');
        Route::get('reports/rooming/print', [ExportController::class, 'roomingPrint'])->name('reports.rooming.print');
    });

    Route::livewire('admin/audit-logs', 'pages::admin.audit-logs.index')
        ->middleware('can:view-audit-logs')
        ->name('admin.audit-logs.index');

    Route::middleware('can:manage-finance')->group(function () {
        Route::livewire('finance', 'pages::finance.index')->name('finance.index');
        Route::get('reports/payments/export', [ExportController::class, 'payments'])->name('reports.payments.export');
        Route::get('reports/donations/export', [ExportController::class, 'donations'])->name('reports.donations.export');
    });
});

require __DIR__.'/settings.php';
