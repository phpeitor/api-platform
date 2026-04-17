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
    ];

    protected $hidden = [
        'token', // Ocultar el token en respuestas JSON
    ];

    protected $dates = [
        'last_used_at',
        'created_at',
        'updated_at',
    ];
}
