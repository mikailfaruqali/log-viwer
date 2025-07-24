<?php

use Illuminate\Support\Facades\Route;
use Snawbar\LogViewer\Http\Controllers\LogViewerController;

Route::get('/', [LogViewerController::class, 'index'])->name('log-viewer.index');
Route::delete('/delete', [LogViewerController::class, 'delete'])->name('log-viewer.delete');
