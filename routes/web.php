<?php

use App\Http\Controllers\PayController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Http\Controllers\HomeController;
use Livewire\Volt\Volt;

Volt::route('/', 'users.index');

Volt::route('admin/service', 'service.index')->name('service.index')
    ->middleware(AdminMiddleware::class);

Volt::route('admin/prompts', 'prompt.index')->name('prompt.index')
    ->middleware(AdminMiddleware::class);

Volt::route('admin/buttons', 'telegram.index')
    ->middleware(AdminMiddleware::class);

Volt::route('admin/ai', 'ai.index')
    ->middleware(AdminMiddleware::class);

Volt::route('admin/plans', 'plans.index')
    ->middleware(AdminMiddleware::class);


Volt::route('admin/login', 'users.index')
    ->name('login')
    ->withoutMiddleware(AdminMiddleware::class);

Route::any('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('admin/login');
})->withoutMiddleware(AdminMiddleware::class);


Route::get('/home', function () {
    return view('home');
})->name('home');


Route::get('/terms', function () {
    return view('terms-and-conditions');
})->name('terms');


Route::any('pay/calback',[PayController::class,'calback'])->name('pay.calback');