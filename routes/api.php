<?php

use App\Http\Controllers\AuthController;
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

Route::post('authenticate', [AuthController::class, 'authenticate']);

Route::group(['prefix' => 'loans',  'middleware' => 'auth:sanctum'], function()
{
    Route::post('request', [LoanController::class, 'request'])->middleware(['auth:sanctum', 'abilities:request-loan']);
    Route::get('', [LoanController::class, 'all'])->middleware(['auth:sanctum', 'abilities:view-loan']);
    Route::get('{id}', [LoanController::class, 'show'])->middleware(['auth:sanctum', 'abilities:view-loan']);
    Route::post('{id}/approve', [LoanController::class, 'approve'])->middleware(['auth:sanctum', 'abilities:approve-loan']);
    Route::post('{id}/repay', [RepaymentController::class, 'repay'])->middleware(['auth:sanctum', 'abilities:repay-loan']);
});
