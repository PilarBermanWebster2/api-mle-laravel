<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SummarizerController;

Route::get('/', [SummarizerController::class, 'index'])->name('summarizer.index');
Route::post('/summarize', [SummarizerController::class, 'summarize'])->name('summarize');


