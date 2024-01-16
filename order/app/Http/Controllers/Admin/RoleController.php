<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stores;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|string|max:255',
            'permissions' => 'array',
        ], [
            "name.required" => "Hãy nhập tên nhóm quyền",
            "name.unique" => "Nhóm quyền đã tồn tại",
            "prd_name.max" => "Ký tự tối đa là 255",
        ]);
        $role = Role::create($request->all());
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('name', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }
        return response()->json($role, 201);
    }
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {

        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Không tìm thấy vai trò'], 404);
        }
        $data = $request->only(['name', 'note', 'status']);
        $role->update($data);
        $role->syncPermissions($request->get('permissions'));
        return response()->json(['message' => 'Cập nhật vai trò thành công'], 200);
    }
    public function getRolesUser(Request $request, $userID)
    {
        $user = User::find($userID);
        $roles = $user->roles;
        $stores = Stores::paginate(50);
        if (!$user) {
            return response()->json(['error' => 'Người dùng không tồn tại'], 404);
        }
        $rolePermissions = [];
        if ($user->permissions) {
            $userPermissions = json_decode($user->permissions, true);
            $rolePermissions = array_merge($rolePermissions, $userPermissions);
        }
        return response()->json(['permissions' => $rolePermissions ?: [], 'stores' => $stores, 'user' => $user , 'roles' => $roles]);
    }
    public function showDataRoles(Request $request, $id){
        $role = Role::find($id);
        $users = User::all();
        $stores = Stores::all();
        $UserRoles = $role->users;
        return response()->json(['UserRoles' => $UserRoles,'stores' => $stores]);
    }
    public function getUserRoles()
    {
        $user =  Auth::user();
        $roles = $user->roles()->get()->pluck('name');
        return response()->json(['roles' => $roles]);
    }
}
