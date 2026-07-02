<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role', 'created_at']);

        return inertia('Admin/Users/Index', ['users' => $users]);
    }

    public function create()
    {
        return inertia('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'pm'])],
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect('/admin/users');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot delete your own account.');
        }
        $user->delete();

        return redirect('/admin/users');
    }
}
