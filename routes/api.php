<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Api\BuildingController;

Route::post('/register', [AuthController::class , 'register']);
Route::post('/login', [AuthController::class , 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class , 'me']);
    
    // User management routes
    Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
    Route::post('/users', [\App\Http\Controllers\Api\UserController::class, 'store']);
    Route::put('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);

    Route::get('/online-users', [AuthController::class , 'onlineUsers']);
    
    // Profile routes
    Route::post('/profile/update', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::post('/profile/upload-photo', [\App\Http\Controllers\Api\ProfileController::class, 'uploadPhoto']);
    
    Route::get('/inventory/history', [InventoryController::class , 'history']);

    // Projects API
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class , 'index']);
    Route::post('/projects', [\App\Http\Controllers\ProjectController::class , 'store']);
    Route::put('/projects/{id}', [\App\Http\Controllers\ProjectController::class , 'update']);
    Route::delete('/projects/{id}', [\App\Http\Controllers\ProjectController::class , 'destroy']);

    // Project-Scoped Routes (Require X-Project-Id header)
    Route::middleware('project.scope')->group(function () {
            // Inventory Routes
            Route::get('/inventory', [InventoryController::class , 'index']);
            Route::post('/inventory', [InventoryController::class , 'store']);
            Route::put('/inventory/{id}', [InventoryController::class , 'update']);
            Route::delete('/inventory/{id}', [InventoryController::class , 'destroy']);
            Route::post('/inventory/stock-in', [InventoryController::class , 'stockIn']);
            Route::post('/inventory/stock-out', [InventoryController::class , 'stockOut']);
            Route::post('/inventory/return-tool', [InventoryController::class , 'returnTool']);

            // Building Progress Routes
            Route::get('/buildings', [BuildingController::class , 'index']);
            Route::get('/buildings/{id}', [BuildingController::class , 'show']);
            Route::post('/buildings/{id}/elevation', [BuildingController::class , 'uploadElevationImage']);
            Route::post('/buildings/{buildingId}/floors', [BuildingController::class , 'storeFloor']);
            Route::delete('/floors/{id}', [BuildingController::class , 'deleteFloor']);
            Route::get('/floors/{id}', [BuildingController::class , 'showFloor']);
            Route::post('/floors/{id}/image', [BuildingController::class , 'uploadFloorImage']);
            Route::post('/floors/{id}/zones', [\App\Http\Controllers\Api\ZoneController::class , 'store']);
            Route::delete('/zones/{id}', [\App\Http\Controllers\Api\ZoneController::class , 'destroy']);

            // Map & Annotations Routes
            Route::get('/legends', [\App\Http\Controllers\Api\LegendController::class , 'index']);
            Route::post('/legends', [\App\Http\Controllers\Api\LegendController::class , 'store']);
            Route::delete('/legends/{id}', [\App\Http\Controllers\Api\LegendController::class , 'destroy']);
            Route::get('/annotations', [\App\Http\Controllers\Api\PlanAnnotationController::class , 'index']);
            Route::post('/annotations', [\App\Http\Controllers\Api\PlanAnnotationController::class , 'store']);
            Route::get('/objects/all', [\App\Http\Controllers\Api\ObjectComponentController::class , 'all']);
            Route::get('/objects', [\App\Http\Controllers\Api\ObjectComponentController::class , 'index']);
            Route::post('/objects', [\App\Http\Controllers\Api\ObjectComponentController::class , 'store']);
            Route::post('/objects/{id}/ports', [\App\Http\Controllers\Api\ObjectComponentController::class , 'addPort']);
            Route::put('/objects/{id}/ports/{portId}', [\App\Http\Controllers\Api\ObjectComponentController::class , 'updatePort']);
            Route::delete('/objects/{id}/ports/{portId}', [\App\Http\Controllers\Api\ObjectComponentController::class , 'deletePort']);
            Route::patch('/objects/{id}/ports/{portId}/link', [\App\Http\Controllers\Api\ObjectComponentController::class , 'establishLink']);
            Route::post('/objects/{id}/status', [\App\Http\Controllers\Api\ObjectComponentController::class , 'updateStatus']);
            Route::patch('/objects/{id}', [\App\Http\Controllers\Api\ObjectComponentController::class , 'update']);
            Route::delete('/objects/{id}', [\App\Http\Controllers\Api\ObjectComponentController::class , 'destroy']);

            // Bill of Quantity & Topology
            Route::get('/boq/summary', [\App\Http\Controllers\Api\BoqController::class , 'getSummary']);
            Route::get('/boq/cables', [\App\Http\Controllers\Api\BoqController::class , 'getCableTopology']);

            // CSV Upload Routes
            Route::get('/boq-csv', [\App\Http\Controllers\Api\BoqCsvController::class , 'index']);
            Route::post('/boq-csv', [\App\Http\Controllers\Api\BoqCsvController::class , 'store']);
            Route::put('/boq-csv-items/{id}', [\App\Http\Controllers\Api\BoqCsvController::class , 'updateItemStatus']);

            // Scheduling Routes
            Route::apiResource('schedules', \App\Http\Controllers\Api\ScheduleController::class);
            Route::apiResource('attendance', \App\Http\Controllers\Api\AttendanceController::class);
            Route::apiResource('inspection-reports', \App\Http\Controllers\Api\InspectionReportController::class);
            Route::apiResource('maintenance-reports', \App\Http\Controllers\Api\MaintenanceReportController::class);
        }
        );
    });

Route::middleware('auth:sanctum')->get('/user', [AuthController::class , 'me']);