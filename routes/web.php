<?php

use Agentoom\AccessibilityWidget\Livewire\Admin\AccessibilityWidgetCreateEdit;
use Agentoom\AccessibilityWidget\Livewire\Admin\AccessibilityWidgetIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('accessibility-widgets', AccessibilityWidgetIndex::class)->name('accessibility-widgets.index');
    Route::get('accessibility-widgets/create', AccessibilityWidgetCreateEdit::class)->name('accessibility-widgets.create');
    Route::get('accessibility-widgets/{widget}/edit', AccessibilityWidgetCreateEdit::class)->name('accessibility-widgets.edit');
});
