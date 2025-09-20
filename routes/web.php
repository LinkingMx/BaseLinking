<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EmailTemplatePreviewController;
use App\Http\Controllers\TemplateBuilderController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Ruta para guardar plantillas desde TemplateBuilder
    Route::post('filament/save-template', [TemplateBuilderController::class, 'saveTemplate'])
        ->name('filament.admin.save-template');
});

// Rutas para preview de email templates
Route::prefix('email-preview')->name('email.preview.')->group(function () {
    Route::get('wrapper', [EmailTemplatePreviewController::class, 'showWrapper'])
        ->name('wrapper');
    
    Route::get('template/{templateKey}', [EmailTemplatePreviewController::class, 'showTemplate'])
        ->name('template');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
