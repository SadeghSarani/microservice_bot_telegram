<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prompt extends Model
{
    protected $fillable = [
        'service_id',
        'prompt'
    ];

    public function service() : BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

}
