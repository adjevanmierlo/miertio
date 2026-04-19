<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Label;
use Illuminate\Http\Request;

class CardController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'column_id' => 'required|exists:columns,id',
      'board_id'  => 'required|exists:boards,id',
      'title'     => 'required|string|max:255',
    ]);

    $position = Card::where('column_id', $request->column_id)->max('position') + 1;

    return Card::create([
      'column_id' => $request->column_id,
      'board_id'  => $request->board_id,
      'title'     => $request->title,
      'position'  => $position,
    ]);
  }

  public function update(Request $request, Card $card)
  {
    $request->validate([
      'title'       => 'sometimes|string|max:255',
      'description' => 'nullable|string',
      'due_date'    => 'nullable|date',
      'cover_color' => 'nullable|string|size:7',
      'priority'    => 'sometimes|in:low,normal,high,urgent',
    ]);

    $card->update($request->only('title', 'description', 'due_date', 'cover_color', 'priority'));
    return $card->load('checklistItems', 'labels');
  }

  public function destroy(Card $card)
  {
    $card->delete();
    return response()->noContent();
  }

  public function move(Request $request, Card $card)
  {
    $request->validate([
      'column_id' => 'required|exists:columns,id',
      'position'  => 'required|integer',
    ]);

    $card->update([
      'column_id' => $request->column_id,
      'position'  => $request->position,
    ]);

    return $card;
  }

  public function reorder(Request $request)
  {
    $request->validate([
      'cards'   => 'required|array',
      'cards.*' => 'exists:cards,id',
    ]);

    foreach ($request->cards as $position => $id) {
      Card::where('id', $id)->update(['position' => $position]);
    }

    return response()->noContent();
  }

  public function attachLabel(Card $card, Label $label)
  {
    $card->labels()->syncWithoutDetaching([$label->id]);
    return response()->noContent();
  }

  public function detachLabel(Card $card, Label $label)
  {
    $card->labels()->detach($label->id);
    return response()->noContent();
  }
}
