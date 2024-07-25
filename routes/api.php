<?php

use Illuminate\Support\Facades\Route;
use App\Models\City;
use App\Models\RecommendationTemplate;

Route::post('/v1/calculate', [
    \App\Http\Controllers\Api\v1\DSSCalculationController::class,
    'calculate'
]);

Route::get('/v1/city', function () {
    $cities = City::select([
        'id',
        'name'
    ])->get();

    return response()->json($cities);
});

Route::get('v1/recommendation-template', function () {
    $templates = RecommendationTemplate::select([
        'id',
        'name'
    ])->get();

    return response()->json($templates);
});
