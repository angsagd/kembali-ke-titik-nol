<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('alumni/profile', 'pages::alumni.profile')
        ->middleware('can:update-own-alumni-profile')
        ->name('alumni.profile');

    Route::middleware('can:view-alumni-directory')->group(function () {
        Route::livewire('alumni/directory', 'pages::alumni.directory.index')->name('alumni.directory.index');
        Route::livewire('alumni/directory/{alumni}', 'pages::alumni.directory.show')->name('alumni.directory.show');
    });

    Route::middleware('can:manage-alumni')->group(function () {
        Route::livewire('admin/alumni', 'pages::admin.alumni.index')->name('admin.alumni.index');
        Route::livewire('admin/alumni/{alumni}', 'pages::admin.alumni.show')->name('admin.alumni.show');
    });
});

require __DIR__.'/settings.php';
