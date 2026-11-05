<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['nama', 'username', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function arsips(): HasMany
    {
        return $this->hasMany(Arsip::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function pins(): BelongsToMany
    {
        return $this->belongsToMany(Arsip::class, 'arsip_pin', 'user_id', 'arsip_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
