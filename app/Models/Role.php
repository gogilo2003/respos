<?php

namespace App\Models;

use App\Services\PermissionRegistry;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $table = 'roles';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Check if this role possesses a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $perms = $this->permissions;

        if (is_null($perms)) {
            $perms = app(PermissionRegistry::class)->getDefaultPermissionsForRole($this->name);
        }

        return is_array($perms) && in_array($permission, $perms, true);
    }
}
