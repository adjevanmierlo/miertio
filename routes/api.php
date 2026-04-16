<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ChecklistItemController;

// Boards
Route::apiResource('boards', BoardController::class);

// Columns
Route::post('columns', [ColumnController::class, 'store']);
Route::put('columns/{column}', [ColumnController::class, 'update']);
Route::delete('columns/{column}', [ColumnController::class, 'destroy']);
Route::post('columns/reorder', [ColumnController::class, 'reorder']);

// Cards
Route::post('cards', [CardController::class, 'store']);
Route::put('cards/{card}', [CardController::class, 'update']);
Route::delete('cards/{card}', [CardController::class, 'destroy']);
Route::post('cards/{card}/move', [CardController::class, 'move']);
Route::post('cards/reorder', [CardController::class, 'reorder']);

// Checklist items
Route::post('checklist-items', [ChecklistItemController::class, 'store']);
Route::put('checklist-items/{checklistItem}', [ChecklistItemController::class, 'update']);
Route::delete('checklist-items/{checklistItem}', [ChecklistItemController::class, 'destroy']);
