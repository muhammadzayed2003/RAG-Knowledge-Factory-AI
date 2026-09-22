<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSource extends Model
{
    protected $fillable = [
        'url',
        'host',
        'page_count',
        'chunk_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'page_count' => 'integer',
        'chunk_count' => 'integer',
    ];
}