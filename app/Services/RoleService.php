<?php

namespace App\Services;

use App\Models\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    private $actions = ['list', 'modify'];

    private $hiddenModules = ['roles', 'permissions'];

    private $moduleActionMap = [
        'dashboard' => ['list'],
        'project-manipulation' => ['list', 'modify'],
        'associate-tree' => ['list'],
        'associate-create' => ['list', 'modify'], 
        'associate-details' => ['list', 'modify'],
        'direct-associate' => ['list'],
        'associate-downline' => ['list'],
        'promotion.report' => ['list'],
        'customer-list' => ['list'],
    ];

    public function getActions($slug = null)
    {
        if (! $slug) {
            return $this->actions;
        }

        return array_key_exists($slug, $this->moduleActionMap)
            ? $this->moduleActionMap[$slug]
            : $this->actions;
    }

    public function getModules()
    {
        return Module::whereNull('parent_id')
            ->whereNotIn('slug', $this->hiddenModules)
            ->with(['children' => function ($query) {
                $query->whereNotIn('slug', $this->hiddenModules);
            }])
            ->orderBy('sort_order')
            ->get();
    }

    public function getRoles()
    {
        $loggedInRole = auth()->user()->roles->first()?->name;

        return Role::with('permissions')->where('name', '!=', $loggedInRole)->latest()->get();
    }

    // public function getModules()
    // {
    //     return Module::whereNull('parent_id')->whereNotIn('slug', $this->hiddenModules)->with(['children' => function ($query) {
    //         $query->whereNotIn('slug', $this->hiddenModules);
    //     },
    //     ])
    //         ->orderBy('sort_order')->get();
    // }

    public function createRole(array $data)
    {
        $role = Role::create(['name' => $data['name']]);
        if (! empty($data['permissions'])) {
            foreach (
                $data['permissions'] as $permissionName
            ) {
                Permission::firstOrCreate(['name' => $permissionName]);
            }
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function findRole($id)
    {
        return Role::findOrFail($id);
    }

    public function getRolePermissions($role)
    {
        return $role->permissions->pluck('name')->toArray();
    }

    public function updateRole($id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $data['name']]);
        if (! empty($data['permissions'])) {
            foreach (
                $data['permissions'] as $permissionName
            ) {
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function deleteRole($id)
    {
        return Role::findOrFail($id)->delete();
    }
}
