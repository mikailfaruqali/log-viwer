<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Snawbar\LogViewer\Http\Controllers\LogViewerController;

Route::get('/', [LogViewerController::class, 'index'])->name('log-viewer.index');
Route::get('/download', [LogViewerController::class, 'download'])->name('log-viewer.download');
Route::delete('/delete', [LogViewerController::class, 'delete'])->name('log-viewer.delete');
