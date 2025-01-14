<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBot extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'context',
        'answer'
    ];


    public function service() : BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
