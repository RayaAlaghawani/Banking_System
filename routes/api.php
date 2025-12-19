
<?php

use App\Http\Controllers\Auth\CitizenAuthController;
//use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SupportTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/citizen/register', [CitizenAuthController::class, 'register']);
Route::post('/citizen/verify-email/{user_id}', [CitizenAuthController::class, 'verifyEmail']);
Route::post('login', [CitizenAuthController::class, 'login'])->
middleware('role.throttle');
Route::middleware('auth:sanctum')->group(function () {
    //حسابات
    Route::post('add_client', [\App\Http\Controllers\AccountController::class,
        'add_client']);
    Route::post('add_employee', [\App\Http\Controllers\EmployeeController::class,
        'add_employee']);

    Route::post('createMainAccount', [\App\Http\Controllers\AccountController::class,
        'createMainAccount']);
    Route::post('addChildAccount', [\App\Http\Controllers\AccountController::class,
        'addChildAccount']);
    //معاملات
        Route::post('store', [\App\Http\Controllers\TransactionController::class,
            'store']);
    Route::post('logout',[CitizenAuthController::class,'logout']);

    //تذاكر الدعم
    // تذاكر الدعم
    Route::get('tickets', [SupportTicketController::class, 'index']);
    Route::post('addtickets', [SupportTicketController::class, 'store']);
    Route::get('tickets/{id}', [SupportTicketController::class, 'show']);
    Route::post('replytickets/{id}', [SupportTicketController::class, 'reply']); // عدلت المسار قليلاً ليكون أكثر وضوحاً

    // مسار تعيين الموظف (للموظفين فقط)
    Route::post('tickets/{id}/assign', [SupportTicketController::class, 'assignAgent']);

    // مسار تغيير الحالة (للموظفين فقط)
    Route::patch('tickets/{id}/status', [SupportTicketController::class, 'changeStatus']);
});

