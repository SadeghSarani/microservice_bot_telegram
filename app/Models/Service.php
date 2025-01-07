<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{

    protected $fillable = [
        'name',
        'description',
        ''
    ];


    public function prompt() : HasMany
    {
        return $this->hasMany(Prompt::class);
    }

}
