<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('loans/request', [LoanController::class, 'request']);
Route::get('loans', [LoanController::class, 'all']);
Route::get('loans/{id}', [LoanController::class, 'show']);
Route::post('loans/{id}/approve', [LoanController::class, 'approve']);
Route::post('loans/{id}/repay', [RepaymentController::class, 'repay']);
