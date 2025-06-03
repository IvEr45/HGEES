<?php

use App\Http\Controllers\MarkerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;
use App\Http\Controllers\PolygonController;
Route::get('/', function () {
    return view('map');
});

// Marker CRUD routes
Route::get('/markers', [MarkerController::class, 'index']);
Route::post('/markers', [MarkerController::class, 'store']);
Route::get('/markers/{id}', [MarkerController::class, 'show']);
Route::put('/markers/{id}', [MarkerController::class, 'update']);
Route::delete('/markers/{id}', [MarkerController::class, 'destroy']);


// Polygon CRUD routes
Route::get('/polygons', [PolygonController::class, 'index']);
Route::post('/polygons', [PolygonController::class, 'store']);
Route::get('/polygons/{id}', [PolygonController::class, 'show']);
Route::put('/polygons/{id}', [PolygonController::class, 'update']);
Route::delete('/polygons/{id}', [PolygonController::class, 'destroy']);



// Line CRUD routes
Route::get('/lines', [LineController::class, 'index']);
Route::post('/lines', [LineController::class, 'store']);
Route::get('/lines/{id}', [LineController::class, 'show']);
Route::put('/lines/{id}', [LineController::class, 'update']);
Route::delete('/lines/{id}', [LineController::class, 'destroy']);
