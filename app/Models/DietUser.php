<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DietUser extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'diet_id',
        'user_id',
        'answers_user'
    ];

    public function diet()
    {
        return $this->belongsTo(Diet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts() : array
    {
        return [
          'answers_user' => 'json'
        ];
    }
}
