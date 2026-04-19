<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\LabelController;


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

// Labels
Route::get('labels', [LabelController::class, 'index']);
Route::post('labels', [LabelController::class, 'store']);
Route::put('labels/{label}', [LabelController::class, 'update']);
Route::delete('labels/{label}', [LabelController::class, 'destroy']);

// Card labels
Route::post('cards/{card}/labels/{label}', [CardController::class, 'attachLabel']);
Route::delete('cards/{card}/labels/{label}', [CardController::class, 'detachLabel']);
