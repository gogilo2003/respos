<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\AuditLogService;
use App\Services\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct(
        protected PermissionRegistry $permissionRegistry,
        protected AuditLogService $auditLogService
    ) {}

    public function index()
    {
        Gate::authorize('admin');

        $roles = Role::orderBy('id')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions ?? $this->permissionRegistry->getDefaultPermissionsForRole($role->name),
            ];
        });

        $catalog = $this->permissionRegistry->getAllPermissions();

        // Group catalog by feature group
        $groupedCatalog = [];
        foreach ($catalog as $item) {
            $group = $item['group'];
            if (! isset($groupedCatalog[$group])) {
                $groupedCatalog[$group] = [];
            }
            $groupedCatalog[$group][] = [
                'key' => $item['key'],
                'label' => $item['label'],
                'routeName' => $item['routeName'],
                'showInNav' => $item['nav']['showInNav'] ?? false,
            ];
        }

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'catalogGrouped' => $groupedCatalog,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $oldPermissions = $role->permissions ?? $this->permissionRegistry->getDefaultPermissionsForRole($role->name);

        $role->permissions = array_values(array_unique($validated['permissions']));
        $role->save();

        $this->auditLogService->log(
            'role_permissions_updated',
            'Role',
            $role->id,
            ['old' => $oldPermissions, 'new' => $role->permissions]
        );

        return redirect()->back()->with('message', "Permissions for role '{$role->name}' updated successfully.");
    }
}
