<?php

use Illuminate\Routing\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'users.index');
Volt::route('/service', 'service.index');
Volt::route('/prompts', 'prompt.index');
//Route::route('/service', );
