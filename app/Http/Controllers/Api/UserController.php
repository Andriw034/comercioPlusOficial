<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(private readonly MediaUploader $mediaUploader)
    {
    }

    public function index()
    {
        $query = User::query()->with('store', 'carts', 'orders');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $users = $query->get(['id','name','email','avatar','avatar_url']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'role' => 'sometimes|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? true,
        ]);

        if ($request->hasFile('avatar')) {
            $this->replaceAvatar($user, $request->file('avatar'));
        }

        if (!empty($validated['role'])) {
            try { $user->assignRole($validated['role']); } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data' => $user->fresh(),
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        if (!Auth::attempt($validated, $validated['remember'] ?? false)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }

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
            'avatar' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('avatar')) {
            $this->replaceAvatar($user, $request->file('avatar'));
        }

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
            'status' => $validated['status'] ?? $user->status,
        ]);

        if (!empty($validated['role'])) {
            try { $user->syncRoles([$validated['role']]); } catch (\Throwable $e) {}
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($validated['password']);
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ]);
    }

    public function uploadAvatar(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $this->replaceAvatar($user, $request->file('avatar'));

        return response()->json([
            'success' => true,
            'message' => 'Avatar actualizado correctamente',
            'data' => $user->fresh(),
        ]);
    }

    public function destroy(User $user)
    {
        $this->mediaUploader->deleteImage($user->avatar_public_id ?: $user->avatar_path ?: $user->avatar);

        if ($user->avatar_path && !$this->mediaUploader->isAbsoluteUrl($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return response()->noContent();
    }

    private function replaceAvatar(User $user, $avatar): void
    {
        $this->mediaUploader->deleteImage($user->avatar_public_id ?: $user->avatar_path ?: $user->avatar);

        if ($user->avatar_path && !$this->mediaUploader->isAbsoluteUrl($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $upload = $this->mediaUploader->uploadImage($avatar, "users/{$user->id}/avatar");
        $user->avatar = $upload['url'];
        $user->avatar_path = $upload['path'];
        $user->avatar_url = $upload['url'];
        $user->avatar_public_id = $upload['path'];
        $user->save();
    }
}
