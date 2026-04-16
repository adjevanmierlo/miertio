<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'card_id' => 'required|exists:cards,id',
      'title'   => 'required|string|max:255',
    ]);

    $position = ChecklistItem::where('card_id', $request->card_id)->max('position') + 1;

    return ChecklistItem::create([
      'card_id'  => $request->card_id,
      'title'    => $request->title,
      'position' => $position,
    ]);
  }

  public function update(Request $request, ChecklistItem $checklistItem)
  {
    $request->validate([
      'title'     => 'sometimes|string|max:255',
      'completed' => 'sometimes|boolean',
    ]);

    $checklistItem->update($request->only('title', 'completed'));
    return $checklistItem;
  }

  public function destroy(ChecklistItem $checklistItem)
  {
    $checklistItem->delete();
    return response()->noContent();
  }
}
