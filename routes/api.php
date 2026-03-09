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
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\DashboardController;



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

    // ========== SETTINGS ==========
    // Paramètres généraux
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/category/{category}', [SettingController::class, 'getByCategory']);
    Route::get('/settings/key/{key}', [SettingController::class, 'getByKey']);
    Route::put('/settings/{key}', [SettingController::class, 'update']);
    Route::post('/settings/bulk-update', [SettingController::class, 'bulkUpdate']);
    Route::post('/settings/reset', [SettingController::class, 'reset']);

    // Tranches d'imposition (IRSA)
    Route::get('/settings/tax-brackets', [SettingController::class, 'getTaxBrackets']);
    Route::post('/settings/tax-brackets', [SettingController::class, 'createTaxBracket']);
    Route::put('/settings/tax-brackets/{id}', [SettingController::class, 'updateTaxBracket']);
    Route::delete('/settings/tax-brackets/{id}', [SettingController::class, 'deleteTaxBracket']);

    // Cotisations sociales
    Route::get('/settings/contributions', [SettingController::class, 'getSocialContributions']);
    Route::post('/settings/contributions', [SettingController::class, 'createSocialContribution']);
    Route::put('/settings/contributions/{id}', [SettingController::class, 'updateSocialContribution']);
    Route::delete('/settings/contributions/{id}', [SettingController::class, 'deleteSocialContribution']);

    // Jours fériés
    Route::get('/settings/holidays', [SettingController::class, 'getHolidays']);
    Route::post('/settings/holidays', [SettingController::class, 'createHoliday']);
    Route::put('/settings/holidays/{id}', [SettingController::class, 'updateHoliday']);
    Route::delete('/settings/holidays/{id}', [SettingController::class, 'deleteHoliday']);
    Route::post('/settings/holidays/generate', [SettingController::class, 'generateRecurringHolidays']);

    // Informations système
    Route::get('/settings/system-info', [SettingController::class, 'getSystemInfo']);

    // ========== PAYROLL ==========
    // Fiches de paie
    Route::get('/payroll/payslips', [PayrollController::class, 'index']);
    Route::get('/payroll/payslips/{id}', [PayrollController::class, 'show']);
    Route::post('/payroll/calculate', [PayrollController::class, 'calculate']);
    Route::post('/payroll/generate', [PayrollController::class, 'generate']);
    Route::post('/payroll/generate-bulk', [PayrollController::class, 'generateBulk']);
    Route::post('/payroll/payslips/{id}/finalize', [PayrollController::class, 'finalize']);
    Route::post('/payroll/payslips/{id}/mark-paid', [PayrollController::class, 'markAsPaid']);
    Route::delete('/payroll/payslips/{id}', [PayrollController::class, 'destroy']);

    // Bonus
    Route::get('/payroll/bonuses', [PayrollController::class, 'getBonuses']);
    Route::post('/payroll/bonuses', [PayrollController::class, 'createBonus']);
    Route::put('/payroll/bonuses/{id}', [PayrollController::class, 'updateBonus']);
    Route::delete('/payroll/bonuses/{id}', [PayrollController::class, 'deleteBonus']);

    // Avances
    Route::get('/payroll/advances', [PayrollController::class, 'getAdvances']);
    Route::post('/payroll/advances', [PayrollController::class, 'createAdvance']);
    Route::put('/payroll/advances/{id}', [PayrollController::class, 'updateAdvance']);
    Route::delete('/payroll/advances/{id}', [PayrollController::class, 'deleteAdvance']);

    // Heures supplémentaires
    Route::get('/payroll/overtimes', [PayrollController::class, 'getOvertimes']);
    Route::post('/payroll/overtimes', [PayrollController::class, 'createOvertime']);
    Route::put('/payroll/overtimes/{id}', [PayrollController::class, 'updateOvertime']);
    Route::delete('/payroll/overtimes/{id}', [PayrollController::class, 'deleteOvertime']);

    // Rapports
    Route::get('/payroll/reports/monthly', [PayrollController::class, 'monthlyReport']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/employee/{id}', [DashboardController::class, 'employeeStats']);
    Route::get('/dashboard/activities', [DashboardController::class, 'recentActivities']);
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts']);
});

// PDF Downloads
Route::get('/payroll/payslips/{id}/download', [PayrollController::class, 'downloadPDF']);
Route::get('/payroll/payslips/{id}/view', [PayrollController::class, 'viewPDF']);
Route::post('/payroll/download-monthly-zip', [PayrollController::class, 'downloadMonthlyZip']);
