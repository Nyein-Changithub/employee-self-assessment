<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;


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

    /** Staff = anyone holding at least one role; they may sign in to the admin portal. */
    public function isStaff(): bool
    {
        return $this->roles->isNotEmpty();
    }

    public function roleName(): string
    {
        return $this->roles->pluck('name')->join(', ') ?: 'employee';
    }

    /** Keep the legacy users.role column in step with the assigned Spatie role. */
    public function syncRoleColumn(): void
    {
        $this->forceFill(['role' => $this->roles()->orderBy('name')->value('name') ?? 'employee'])->saveQuietly();
    }

    /** First admin page this user may open (used after login and for the 403 fallback). */
    public function homeRoute(): ?string
    {
        foreach ([
            'dashboard.view' => 'admin.dashboard',
            'submissions.view' => 'admin.assessments.index',
            'periods.manage' => 'admin.periods.index',
            'departments.manage' => 'admin.departments.index',
            'positions.manage' => 'admin.positions.index',
            'users.manage' => 'admin.users.index',
            'roles.manage' => 'admin.roles.index',
            'permissions.manage' => 'admin.permissions.index',
        ] as $permission => $route) {
            if ($this->can($permission)) {
                return $route;
            }
        }

        return null;
    }
}
