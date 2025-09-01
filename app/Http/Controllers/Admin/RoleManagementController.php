<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin']);
    }

    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        $availableRoles = [
            'super-admin' => 'Super Administrador',
            'comerciante' => 'Comerciante',
            'cliente' => 'Cliente'
        ];

        return view('admin.roles.index', compact('users', 'availableRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:super-admin,comerciante,cliente'
        ]);

        // Prevenir que el último super-admin pierda su rol
        if ($user->role === 'super-admin' && $request->role !== 'super-admin') {
            $superAdminCount = User::where('role', 'super-admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Debe haber al menos un super-administrador en el sistema.');
            }
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        Log::info('Cambio de rol realizado', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'old_role' => $oldRole,
            'new_role' => $request->role,
            'changed_by' => auth()->id()
        ]);

        return redirect()->back()
            ->with('success', "Rol de {$user->name} actualizado de {$oldRole} a {$request->role}");
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        
        $users = User::where('id', '!=', auth()->id())
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        $availableRoles = [
            'super-admin' => 'Super Administrador',
            'comerciante' => 'Comerciante',
            'cliente' => 'Cliente'
        ];

        return view('admin.roles.index', compact('users', 'availableRoles'));
    }
}
