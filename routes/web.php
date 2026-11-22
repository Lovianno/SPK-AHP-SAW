<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AhpController;
use App\Http\Controllers\AhpComparisonController;
use App\Http\Controllers\SawController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlternativeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('/ahp-comparisons', AhpComparisonController::class)->except('show')->names('ahp-comparisons');
    Route::get('/ahp', [AhpController::class, 'index'])->name('ahp.index');
    Route::post('/ahp/calculate', [AhpController::class, 'calculate'])->name('ahp.calculate');
    Route::get('/saw', [SawController::class, 'index'])->name('saw.index');
    Route::post('/saw/calculate', [SawController::class, 'calculate'])->name('saw.calculate');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/users', UserController::class)->names('users');
    Route::resource('/alternatives', AlternativeController::class)->names('alternatives');
    Route::resource('/criteria', CriterionController::class)->parameters(['criteria' => 'criterion'])->names('criteria');

});

require __DIR__ . '/auth.php';
