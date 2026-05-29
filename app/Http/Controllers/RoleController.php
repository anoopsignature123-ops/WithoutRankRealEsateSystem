<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getRoles();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $modules = $this->roleService->getModules();
        $actions = $this->roleService->getActions();

        return view('roles.create', compact('modules', 'actions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);
        $this->roleService->createRole($request->all());

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function edit($id)
    {
        $role = $this->roleService->findRole($id);
        $modules = $this->roleService->getModules();
        $actions = $this->roleService->getActions();
        $rolePermissions = $this->roleService->getRolePermissions($role);

        return view('roles.edit', compact('role', 'modules', 'actions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $this->roleService->updateRole($id, $request->all());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $this->roleService->deleteRole($id);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}