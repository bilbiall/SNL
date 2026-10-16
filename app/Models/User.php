<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/** Admin accounts only — players never sign up. */
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_admin' => 'boolean'];
    }
}
