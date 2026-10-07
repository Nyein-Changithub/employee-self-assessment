<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN_ROLES = ['ceo', 'gm', 'admin'];

    protected $fillable = [
        'employee_id', 'name', 'email', 'password', 'position', 'department', 'role',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function isAdminRole(): bool
    {
        return in_array($this->role, self::ADMIN_ROLES, true);
    }
}
