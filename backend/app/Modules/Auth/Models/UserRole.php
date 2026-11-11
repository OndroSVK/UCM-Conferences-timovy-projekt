<?php

namespace App\Modules\Auth\Models;

use App\Shared\Auth\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * Global role of an account (participant, superadmin).
 *
 * @property int $user_id
 * @property Role $role
 */
class UserRole extends Model
{
    protected $fillable = ['user_id', 'role'];

    protected function casts(): array
    {
        return ['role' => Role::class];
    }
}
