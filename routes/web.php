<?php

use App\Http\Controllers\Auth\SSOController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});


Route::get('/auth/redirect', [SSOController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/callback', [SSOController::class, 'callback'])->name('sso.callback');

require __DIR__.'/settings.php';
