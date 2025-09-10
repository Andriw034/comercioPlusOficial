<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Obtener todos los usuarios
    public function index()
    {
<<<<<<< HEAD
        $query = User::query()->with('store', 'carts', 'orders');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $users = $query->get(['id','name','email']);

        return response()->json($users);
=======
        $query = User::query();

        // Aplicar scopes para incluir relaciones, filtrar, ordenar y paginar
        $query->included();
        $query->filter();
        $query->sort();

        $users = $query->getOrPaginate();

        return response()->json([
            'status' => 'ok',
            'data' => $users,
        ]);
>>>>>>> 691c95be (comentario)
    }

    // Crear un nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
<<<<<<< HEAD
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'role' => 'sometimes|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? true,
            'avatar' => $avatarPath,
        ]);

        // Asignar rol si fue provisto
        if (!empty($validated['role'])) {
            try { $user->assignRole($validated['role']); } catch (\Throwable $e) { /* opcional */ }
        }

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data' => $user,
        ], 201);
    }

    public function edit(User $user)
    {
        // No aplica para API
=======
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' =>'required|exists:roles,id',
            'status' =>'requirrd |bolean',
            'password' => 'required|string|min:6 | comfirmed',
            'avatar' => 'nullable'
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

       return redirect()->route('users.index')->with('success','usuario creado correctamente');
    }
    
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
>>>>>>> 691c95be (comentario)
    }

    // Obtener un usuario específico
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }

    // Actualizar un usuario
<<<<<<< HEAD
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->getKey(),
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'role' => 'sometimes|string',
            'password' => 'sometimes|nullable|string|min:6',
            'avatar' => 'sometimes|nullable|image|max:2048',
=======
    public function update(Request $request, $user)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
>>>>>>> 691c95be (comentario)
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->update([
<<<<<<< HEAD
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
            'status' => $validated['status'] ?? $user->status,
        ]);

        // Actualizar rol si fue provisto
        if (!empty($validated['role'])) {
            try { $user->syncRoles([$validated['role']]); } catch (\Throwable $e) { /* opcional */ }
        }

        if ($request->filled('password')) {
            $user->password = $validated['password'];
            $user->save();
        }

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ]);
=======
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
>>>>>>> 691c95be (comentario)
    }

    // Eliminar un usuario
    public function destroy(User $user)
    {
<<<<<<< HEAD
        if ($user->avatar) {
=======
      if ($user->avatar) {
>>>>>>> 691c95be (comentario)
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

<<<<<<< HEAD
        return response()->noContent();
=======
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
>>>>>>> 691c95be (comentario)
    }
}
