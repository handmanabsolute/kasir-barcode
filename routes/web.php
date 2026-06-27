<?php

use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['web'])
    ->name('password.email');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
    Route::livewire('categories', 'pages::categories.index')->name('categories.index');
    Route::livewire('products', 'pages::products.index')->name('products.index');
    Route::livewire('transactions', 'pages::transactions.index')->name('transactions.index');
    Route::livewire('reports', 'pages::reports.index')->name('reports.index');
});

require __DIR__.'/settings.php';
