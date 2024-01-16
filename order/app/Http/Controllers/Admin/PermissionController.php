<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stores;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $users = User::query();
        if ($keyword) {
            $users->where(function ($query) use ($keyword) {
                $query->where('display_name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        $users = $users->paginate(50);
        $userRoles = [];
        foreach ($users as $user) {
            $userRoles[$user->id] = $user->getRoleNames()->toArray();
        }
        $stores = Stores::all();
        $permissions = Permission::all();
        $roles = Role::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
            'stores' => $stores,
            'userRoles' => $userRoles,
            'totalPages' => $users->lastPage(),
        ]);
    }
    public function updatePermissions(Request $request, $userID)
    {
        $user = User::find($userID);
        if (!$user) {
            return response()->json(['error' => 'Người dùng không tồn tại'], 404);
        }
        $user->update($request->all());
        $permissions = $request->input('permissions', []);
        $user->syncPermissions($permissions);

        $deniedPermissions = $request->input('denied_permissions', []);
        if (!empty($deniedPermissions)) {
            foreach ($deniedPermissions as $permission) {
                $permissionModel = Permission::where('name', $permission)->first();
                if ($permissionModel) {
                    $user->permissions()->syncWithoutDetaching([$permissionModel->id => ['block' => 1]]);
                }
            }
        }
        $userRoles = $request->input('userRoles', []);
        $user->syncRoles($userRoles);

        return response()->json(['message' => 'Quyền và vai trò đã được cập nhật thành công']);
    }
    public function getUserPermissions()
    {
        $user = Auth::user();
        $permissions = [];
        if ($user->roles) {
            $roles = $user->roles;
            foreach ($roles as $role) {
                $rolePermissions = $role->permissions->pluck('name')->toArray();
                $permissions = array_merge($permissions, $rolePermissions);
            }
        }
        if ($user->permissions) {
            $userPermissions = json_decode($user->permissions, true);
            $userPermissionNames = array_column($userPermissions, 'name');
            $permissions = array_merge($permissions, $userPermissionNames);
        }
        $blockedPermissions = $user->permissions()
            ->wherePivot('block', 1)
            ->pluck('name')
            ->toArray();
        $permissions = array_diff($permissions, $blockedPermissions);
        $allBlockedPermissions = array_merge($rolePermissions, $userPermissionNames, $blockedPermissions);
        $filteredPermissions = array_filter($allBlockedPermissions, function ($permission) use ($blockedPermissions) {
            return !in_array($permission, $blockedPermissions);
        });
        return response()->json(['permissions' => array_unique($filteredPermissions)]);
    }
    public function destroy($id)
    {
        $users = User::find($id);
        if ($users) {
            $users->delete();
            return response()->json('Xóa khách hàng thành công');
        } else {
            return response()->json('Không tìm thấy người dùng', 404);
        }
    }
    public function getPermission(Request $request, $userID)
    {
        $user = User::find($userID);
        $roles = Role::where('status', 0)->get();
        $permissions = $user->permissions()->withPivot('block')->get();
        return response()->json(['roles' => $roles, 'permissions' => $permissions]);
    }
    public function permissionData(Request $request, $id)
    {
        $users = User::find($id);
        $stores = Stores::all();
        $usersAssigned = User::where('user_assigned', $id)->get();
        return response()->json(['users' => $users , 'usersAssigned' => $usersAssigned, 'stores' => $stores]);
    }
    public function permissionDataUser(Request $request, $id)
    {
        $userId = $request->input('selectedUser');
        $userSelect = User::find($userId);
        $user = User::find($id)->id;
        if ($user && $userId) {
            $userSelect->user_assigned = $user; 
            $userSelect->save();
            return response()->json(['message' => 'Thêm người dùng thành công']);
        } else {
            return response()->json(['error' => 'User or selectedUser ID not found'], 404);
        }
    }
    public function permissionDataDelete(Request $request, $id){
        $user = User::find($id);
        if ($user) {
            $user->user_assigned = null;
            $user->save();
    
            return response()->json(['message' => 'User assigned relationship removed successfully']);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    
    public function getUsersAssignedToUser($id, &$assignedUsers = []) {
        $users = User::where('user_assigned', $id)->get();
        foreach ($users as $user) {
            $assignedUsers[] = $user;
            $this->getUsersAssignedToUser($user->id, $assignedUsers);
        }
        $assignedUserIds = array_column($assignedUsers, 'id');
        $orders = Order::whereIn('user_init', $assignedUserIds)->get();
        return response()->json(['assigned_users' => $assignedUsers, 'orders' => $orders]);
    }
    public function getCustomerToUser(){
        $customer = Customer::all();
        return response()->json(['customer' => $customer]);
    }
    public function getCustomerToUserID (Request $request, $id){
        $customerAssigned = Customer::where('user_assigned', $id)->get();
        return response()->json(['customerAssigned' => $customerAssigned]);
    }
    
}
