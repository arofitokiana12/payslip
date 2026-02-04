<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/test', function () {
    return response()->json(['message' => 'bomboclat']);

});


Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // autres routes...
});
