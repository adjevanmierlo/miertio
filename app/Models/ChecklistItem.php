<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
  protected $fillable = ['card_id', 'title', 'completed', 'position'];
}
