<?php

use App\Livewire\BackorderData;
use App\Livewire\ListData;
use App\Livewire\ManageQualityData;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', ListData::class)->name('home');

Route::get('/scrap-defect', ManageQualityData::class)->name('quality.manage');

Route::get('/backorder', BackorderData::class)->name('backorder.manage');