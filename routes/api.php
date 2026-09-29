<?php

use Agentoom\AccessibilityWidget\Http\Controllers\AccessibilityWidgetController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('accessibility-widget.route_prefix'))
    ->middleware('api')
    ->name('accessibility-widget.')
    ->group(function () {
        Route::get('/config/{slug}', [AccessibilityWidgetController::class, 'show'])->name('config');
        Route::get('/loader/{slug}.js', [AccessibilityWidgetController::class, 'loader'])->name('loader');
        Route::get('/widget.js', [AccessibilityWidgetController::class, 'widget'])->name('widget');
    });
