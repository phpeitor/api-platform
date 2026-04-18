<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'api_tokens';
    
    protected $fillable = [
        'name',
        'token',
        'description',
        'expires_at',
    ];

    protected $hidden = [
        'token', // Ocultar el token en respuestas JSON
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
