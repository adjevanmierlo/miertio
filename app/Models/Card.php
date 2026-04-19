<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
  protected $fillable = ['column_id', 'board_id', 'title', 'description', 'position', 'cover_color', 'due_date'];

  public function column()
  {
    return $this->belongsTo(Column::class);
  }

  public function board()
  {
    return $this->belongsTo(Board::class);
  }

  public function checklistItems()
  {
    return $this->hasMany(ChecklistItem::class)->orderBy('position');
  }

  public function labels()
  {
    return $this->belongsToMany(Label::class);
  }
}
