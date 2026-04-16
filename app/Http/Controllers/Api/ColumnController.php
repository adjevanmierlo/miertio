<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'board_id' => 'required|exists:boards,id',
      'title'    => 'required|string|max:255',
    ]);

    $position = Column::where('board_id', $request->board_id)->max('position') + 1;

    return Column::create([
      'board_id' => $request->board_id,
      'title'    => $request->title,
      'position' => $position,
    ]);
  }

  public function update(Request $request, Column $column)
  {
    $request->validate(['title' => 'required|string|max:255']);
    $column->update($request->only('title'));
    return $column;
  }

  public function destroy(Column $column)
  {
    $column->delete();
    return response()->noContent();
  }

  public function reorder(Request $request)
  {
    $request->validate([
      'columns'   => 'required|array',
      'columns.*' => 'exists:columns,id',
    ]);

    foreach ($request->columns as $position => $id) {
      Column::where('id', $id)->update(['position' => $position]);
    }

    return response()->noContent();
  }
}
