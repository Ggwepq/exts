<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/vendor/livewire/livewire.js', $handle);
});

Route::get('link', function () {
    Artisan::call('storage:link');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'pages.user.dashboard.index')->name('dashboard');
    Volt::route('transactions', 'pages.user.transactions.index')->name('user.transactions');
    Volt::route('categories', 'pages.user.categories.index')->name('user.categories');
    Volt::route('recurring', 'pages.user.recurring.index')->name('user.recurring');
    Volt::route('accounts', 'pages.user.accounts.index')->name('user.accounts');
    Volt::route('settings', 'pages.user.settings.index')->name('user.settings');
});
require __DIR__.'/auth.php';
