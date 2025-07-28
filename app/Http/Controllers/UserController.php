<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Mostrar la lista de usuarios
   
    public function index(Request $request)
{
    $query = User::with('role');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('role_id')) {
        $query->where('role_id', $request->role_id);
    }

    $users = $query->paginate(10);

    return view('users.index', compact('users'));
}

    public function welcome(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado === 'activo' ? 1 : 0;
            $query->where('status', $estado);
        }

        if ($request->filled('rol')) {
            $role = $request->rol;
            $query->role($role);
        }

        $users = $query->paginate(10);

        return view('welcome', compact('users'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
          
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
            'password' => bcrypt($validated['password']),
            'avatar' => $avatarPath,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }


    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->getKey(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($validated['password']);
            $user->save();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        // Si tiene avatar, lo eliminamos del almacenamiento
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
  public function show($id)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Método show llamado correctamente',
            'id' => $id
        ]);
    }

    
}
