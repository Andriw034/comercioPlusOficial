<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Mostrar formulario de perfil
     */
    public function edit(Request $request)
    {
        return view('settings.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Manejo del avatar
        if ($request->hasFile('avatar')) {
            // Si ya tenía un avatar, lo borramos del storage
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            // Guardamos el nuevo
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Evitamos guardar el campo avatar crudo
        unset($data['avatar']);

        // Actualizamos usuario
        $user->fill($data)->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
