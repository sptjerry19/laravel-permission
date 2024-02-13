<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = DB::table('model_has_roles')
            ->join('users', 'model_id', '=', 'users.id')
            ->join('roles', 'role_id', '=', 'roles.id')
            ->orderBy('users.id')
            ->get(['users.*', 'roles.name as role_name']);


        return response($users);
    }

    /**
     * register user
     */
    public function register(Request $request)
    {
        $fieds = $request->validate([
            'name' => 'required|string|',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
        ]);

        $role = Role::findByName('viewer');
        $permission = Permission::findByName('view films');
        $role->givePermissionTo($permission);

        $user = User::create([
            'name' => $fieds['name'],
            'email' => $fieds['email'],
            'password' => bcrypt($fieds['password']),
        ]);

        $user->assignRole($role);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'permission' => $permission,
        ];

        return response($response, 201);
    }

    /**
     * login user
     */
    public function login(Request $request)
    {
        $fieds = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fieds['email'])->first();


        if (!$user || !Hash::check($fieds['password'], $user->password)) {
            return response([
                'message' => 'Error login',
            ], 401);
        }

        $role = DB::table('model_has_roles')
            ->join('roles', 'role_id', '=', 'roles.id')
            ->join('users', 'model_id', '=', 'users.id')
            ->where('users.id', '=', $user->id)
            ->get(['users.*', 'roles.name as role_name']);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $role,
            'token' => $token,
        ];
        return response($response, 201);
    }

    /**
     * logout user
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->noContent();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fieds = $request->validate([
            'name' => 'required|string|',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
            'role' => 'required|string',
            'permission' => 'nullable',
        ]);

        $user = User::create([
            'name' => $fieds['name'],
            'email' => $fieds['email'],
            'password' => bcrypt($fieds['password']),
        ]);

        if (!Role::findByName($fieds['role'])) {
            Role::create(['name' => $fieds['role']]);
        }

        $user->assignRole($fieds['role']);
        $role = Role::findByName($fieds['role']);
        $permission = Permission::findByName($fieds['permission']);
        $role->givePermissionTo($permission);

        $response = [
            'user' => $user,
            'role' => $role,
            'permission' => $permission,
        ];

        return response($response, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        User::findOrFail($id);

        $user = DB::table('model_has_permissions')
            ->join('users', 'model_id', '=', $id)
            ->join('permissions', 'permission_id', '=', 'permissions.id')
            ->get(['users.*', 'permissions.name as permissions']);

        return response($user, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
