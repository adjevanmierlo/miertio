<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
  public function index(Request $request)
  {
    $request->validate(['board_id' => 'required|exists:boards,id']);
    return Label::where('board_id', $request->board_id)->get();
  }

  public function store(Request $request)
  {
    $request->validate([
      'board_id' => 'required|exists:boards,id',
      'name'     => 'required|string|max:255',
      'color'    => 'required|string|size:7',
    ]);

    return Label::create($request->only('board_id', 'name', 'color'));
  }

  public function update(Request $request, Label $label)
  {
    $request->validate([
      'name'  => 'sometimes|string|max:255',
      'color' => 'sometimes|string|size:7',
    ]);

    $label->update($request->only('name', 'color'));
    return $label;
  }

  public function destroy(Label $label)
  {
    $label->delete();
    return response()->noContent();
  }
}
