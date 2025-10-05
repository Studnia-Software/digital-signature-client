<?php

use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;


Route::get('/', [SignatureController::class, 'index'])->name('signature.index');
Route::post('/signature/sign', [SignatureController::class, 'sign'])->name('signature.sign');
Route::post('/signature/open', [SignatureController::class, 'openFile'])->name('signature.open');

Route::get('/signature/verify', [SignatureController::class, 'verifyView'])->name('signature.verify');
Route::post('/signature/verify', [SignatureController::class, 'verifyDocument'])->name('signature.verify.check');
