<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
  protected $fillable = ['title'];

  public function columns()
  {
    return $this->hasMany(Column::class)->orderBy('position');
  }
}
