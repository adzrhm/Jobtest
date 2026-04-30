<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/map', function () {
    return view('map');
});

// DATA DASAR 
Route::get('/kabupaten', [MapController::class, 'kabupaten']);
Route::get('/kecamatan/{kabkot}', [MapController::class, 'kecamatan']);
Route::get('/desa-list/{kecamatan}', [MapController::class, 'desaByKecamatan']);

// GEOJSON
Route::get('/desa', [MapController::class, 'desa']);
Route::get('/tuplah', [MapController::class, 'tuplah']);
Route::get('/grid-desa/{desa}', [MapController::class, 'tuplahByDesa']);

// FILTER
Route::get('/filter-kabupaten/{kabkot}', [MapController::class, 'filterKabupaten']);
Route::get('/filter-kecamatan/{kecamatan}', [MapController::class, 'filterKecamatan']);
Route::get('/filter-desa/{desa}', [MapController::class, 'filterDesa']);

// KATEGORI
Route::get('/kategori', [MapController::class, 'kategori']); 