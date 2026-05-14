<?php

use App\Livewire\ListData;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', ListData::class);
