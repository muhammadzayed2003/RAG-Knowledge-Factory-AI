<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'chunk_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'chunk_count' => 'integer',
    ];
}