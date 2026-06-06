<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function company(Request $request): View
    {
        $tenant = Auth::user()->tenant;
        return view('settings.company', compact('tenant'));
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        Auth::user()->tenant->update($validated);

        return redirect()->route('settings.company')->with('success', 'Datos de la empresa actualizados.');
    }

    public function users(Request $request): View
    {
        $users = User::where('tenant_id', Auth::user()->tenant_id)
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('settings.users', compact('users', 'roles'));
    }

    public function createUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => Auth::user()->tenant_id,
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('settings.users')->with('success', "Usuario '{$user->name}' creado exitosamente.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.users')->with('error', 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('settings.users')->with('success', "Usuario '{$user->name}' actualizado.");
    }

    public function deleteUser(User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.users')->with('error', 'Acceso no autorizado.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('settings.users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('settings.users')->with('success', "Usuario '{$name}' eliminado.");
    }

    public function roles(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('settings.roles', compact('roles', 'permissions'));
    }

    public function createRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('settings.roles')->with('success', "Rol '{$role->name}' creado exitosamente.");
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('settings.roles')->with('success', "Permisos del rol '{$role->name}' actualizados.");
    }

    public function clauses(): View
    {
        $clauses = TenantClause::orderBy('sort_order')->get();
        return view('settings.clauses', compact('clauses'));
    }

    public function storeClause(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:terms,warranty,privacy,return,other',
            'print_on_receipt' => 'boolean',
        ]);

        $validated['print_on_receipt'] = $request->boolean('print_on_receipt');

        TenantClause::create(array_merge($validated, [
            'tenant_id' => Auth::user()->tenant_id,
        ]));

        return redirect()->route('settings.clauses')->with('success', 'Cláusula creada exitosamente.');
    }

    public function updateClause(Request $request, TenantClause $clause): RedirectResponse
    {
        if ($clause->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.clauses')->with('error', 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:terms,warranty,privacy,return,other',
            'is_active' => 'boolean',
            'print_on_receipt' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['print_on_receipt'] = $request->boolean('print_on_receipt');

        $clause->update($validated);

        return redirect()->route('settings.clauses')->with('success', 'Cláusula actualizada.');
    }

    public function deleteClause(TenantClause $clause): RedirectResponse
    {
        if ($clause->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.clauses')->with('error', 'Acceso no autorizado.');
        }

        $clause->delete();

        return redirect()->route('settings.clauses')->with('success', 'Cláusula eliminada.');
    }

    public function taxes(): View
    {
        $tenant = Auth::user()->tenant;
        return view('settings.taxes', compact('tenant'));
    }

    public function updateTaxes(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_enabled' => 'boolean',
            'tax_percentage' => 'numeric|min:0|max:100',
            'tax_mode' => 'in:per_item,on_total',
            'print_format' => 'in:ticket_58mm,ticket_80mm,a4',
            'work_order_prefix' => 'string|max:20',
        ]);

        $validated['tax_enabled'] = $request->boolean('tax_enabled');

        Auth::user()->tenant->update($validated);

        return redirect()->route('settings.taxes')->with('success', 'Configuración actualizada.');
    }
}
