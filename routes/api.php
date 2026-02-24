<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;

Route::get('/test', function () {
    return response()->json(['message' => 'bomboclat']);
});


Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('positions', PositionController::class);
    /*
        GET    /api/positions              → index
        POST   /api/positions              → store
        GET    /api/positions/{position}   → show
        PUT    /api/positions/{position}   → update
        DELETE /api/positions/{position}   → destroy
    */

    // --------------- EMPLOYEES ---------------
    Route::apiResource('employees', EmployeeController::class);
    /*
        GET    /api/employees              → index
        POST   /api/employees              → store
        GET    /api/employees/{employee}   → show
        PUT    /api/employees/{employee}   → update
        DELETE /api/employees/{employee}   → destroy
    */

    // --------------- UTILISATEUR CONNECTÉ ---------------
    Route::get('users/me', [UserController::class, 'me']);

    // --------------- ROLES (tous peuvent voir) ---------------
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/system', [RoleController::class, 'systemRoles']);
    Route::get('roles/{role}', [RoleController::class, 'show']);

    // Super_admin uniquement pour créer/modifier/supprimer
    Route::middleware(['role:super_admin'])->group(function () {
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    });

    // --------------- USERS (admin et super_admin) ---------------
    Route::middleware(['role:admin,super_admin'])->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Companies CRUD
    Route::apiResource('companies', CompanyController::class);


    // Attendance CRUD
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
    Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);

    // Pointage rapide
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);

    // Rapport
    Route::get('/attendance/report/{employee_id}', [AttendanceController::class, 'reportByEmployee']);


    // Leaves CRUD
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves/{id}', [LeaveController::class, 'show']);
    Route::put('/leaves/{id}', [LeaveController::class, 'update']);
    Route::delete('/leaves/{id}', [LeaveController::class, 'destroy']);

    // Actions sur les congés
    Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve']);
    Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject']);
    Route::post('/leaves/{id}/cancel', [LeaveController::class, 'cancel']);

    // Vues spéciales
    Route::get('/leaves/pending/list', [LeaveController::class, 'pending']);
    Route::get('/leaves/calendar/view', [LeaveController::class, 'calendar']);

    // Statistiques
    Route::get('/leaves/stats/{employee_id}', [LeaveController::class, 'statsByEmployee']);
});
