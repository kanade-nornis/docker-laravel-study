<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ItemSearchController;

Route::get('/market/price', [MarketController::class, 'price']);
Route::get('/items/search', [ItemSearchController::class, 'search']);