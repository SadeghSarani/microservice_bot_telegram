<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diet extends Model
{
    protected $fillable = [
      'question',
      'question_step'
    ];


    public function user()
    {
        return $this->belongsTo(DietUser::class);
    }
}
