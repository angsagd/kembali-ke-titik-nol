<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:manage-alumni')->group(function () {
        Route::livewire('admin/alumni', 'pages::admin.alumni.index')->name('admin.alumni.index');
        Route::livewire('admin/alumni/{alumni}', 'pages::admin.alumni.show')->name('admin.alumni.show');
    });
});

require __DIR__.'/settings.php';
