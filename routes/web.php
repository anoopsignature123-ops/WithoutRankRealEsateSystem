<?php

use Illuminate\Support\Facades\Route;

Route::get('/get-cities/{stateId}', function (int $stateId, \App\Services\LocationService $locationService) {
    return response()->json(
        $locationService->getCities($stateId)
    );
})->name('get.cities');

require __DIR__ . '/admin.php';
require __DIR__ . '/associate.php';
require __DIR__ . '/customer.php';
