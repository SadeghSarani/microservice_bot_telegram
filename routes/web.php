<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
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

Volt::route('admin/login', 'users.index')
    ->name('login')
    ->withoutMiddleware(AdminMiddleware::class);

Route::any('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('admin/login');
})->withoutMiddleware(AdminMiddleware::class);