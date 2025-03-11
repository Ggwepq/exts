<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Livewire\Livewire;

Route::view('/', 'welcome');

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/vendor/livewire/livewire.js', $handle);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Volt::route('transactions', 'pages.user.transactions')->name('user.transactions');
    Volt::route('categories', 'pages.user.categories')->name('user.categories');
    Volt::route('recurring', 'pages.user.recurring')->name('user.recurring');
    Volt::route('accounts', 'pages.user.accounts')->name('user.accounts');
    Volt::route('settings', 'pages.user.settings')->name('user.settings');
});
require __DIR__ . '/auth.php';
