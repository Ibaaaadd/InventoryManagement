<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query()->withCount('users');

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%']);
        }

        if ($request->has('sort') && $request->has('order')) {
            $sortField = $request->sort;
            $sortDirection = $request->order;

            $allowedSorts = ['name', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            } elseif ($sortField === 'users_count') {
                $query->orderBy('users_count', $sortDirection);
            }
        }

        $roles = $query->paginate(10);

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', Rule::unique('roles', 'name')->whereNull('deleted_at')],
            'description' => 'nullable|string',
        ]);

        $role = Role::create($validated);

        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        $role->loadCount('users');
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', Rule::unique('roles', 'name')->ignore($role->id)->whereNull('deleted_at')],
            'description' => 'nullable|string',
        ]);

        $role->update($validated);

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        if ($role->isProtected()) {
            return response()->json([
                'message' => 'Cannot delete protected role Administrator.'
            ], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role that is still assigned to users.'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.'
        ]);
    }
}
