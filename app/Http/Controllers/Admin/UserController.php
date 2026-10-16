<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->latest()->paginate(20)->withQueryString();
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($request->role);

        ActivityLog::record('create', 'user', "Membuat user: {$user->email} dengan role {$request->role}");

        return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan.']);
    }

    public function edit(User $user)
    {
        $user->load('roles');
        return response()->json([
            'user' => $user,
            'role' => $user->getRoleNames()->first(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        ActivityLog::record('update', 'user', "Memperbarui user: {$user->email}");

        return response()->json(['success' => true, 'message' => 'User berhasil diperbarui.']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        ActivityLog::record('delete', 'user', "Menghapus user: {$user->email}");
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
    }
}
