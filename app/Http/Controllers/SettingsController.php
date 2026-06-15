<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use App\Services\R2StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function updateCompany(Request $request, R2StorageService $r2): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'social_media' => 'nullable|json',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $tenant = Auth::user()->tenant;

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $tenant->phone,
            'email' => $validated['email'] ?? $tenant->email,
            'address' => $validated['address'] ?? $tenant->address,
            'mail_host' => $validated['mail_host'] ?? $tenant->mail_host,
            'mail_port' => $validated['mail_port'] ?? $tenant->mail_port,
            'mail_username' => $validated['mail_username'] ?? $tenant->mail_username,
            'mail_encryption' => $validated['mail_encryption'] ?? $tenant->mail_encryption,
            'mail_from_address' => $validated['mail_from_address'] ?? $tenant->mail_from_address,
            'mail_from_name' => $validated['mail_from_name'] ?? $tenant->mail_from_name,
        ];

        if ($request->filled('mail_password')) {
            $data['mail_password'] = $validated['mail_password'];
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                $r2->delete($tenant->logo);
            }
            $data['logo'] = $r2->upload($request->file('logo'), 'logos');
        }

        if ($request->filled('social_media')) {
            $data['social_media'] = json_decode($validated['social_media'], true);
        } elseif ($request->input('social_media') === null) {
            $data['social_media'] = [];
        }

        $tenant->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Datos de la empresa actualizados correctamente.');
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

        return redirect()->route('settings.index')->with('success', "Usuario '{$user->name}' creado exitosamente.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.index')->with('error', 'Acceso no autorizado.');
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

        return redirect()->route('settings.index')->with('success', "Usuario '{$user->name}' actualizado.");
    }

    public function deleteUser(User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.index')->with('error', 'Acceso no autorizado.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('settings.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('settings.index')->with('success', "Usuario '{$name}' eliminado.");
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

        return redirect()->route('settings.index')->with('success', "Rol '{$role->name}' creado exitosamente.");
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('settings.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function deleteRole(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('settings.index')
                ->with('error', 'No se puede eliminar el rol super-admin.');
        }

        $role->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Rol eliminado correctamente.');
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
            'content' => 'nullable|string|required_without:file',
            'type' => 'required|in:terms,warranty,privacy,return,other',
            'print_on_receipt' => 'boolean',
            'file' => 'nullable|file|mimes:pdf|max:10240|required_without:content',
        ]);

        $validated['print_on_receipt'] = $request->boolean('print_on_receipt');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension() ?: 'pdf';
            $filename = Str::uuid() . '.' . $extension;
            $path = 'clauses/' . date('Y/m') . '/' . $filename;

            Storage::disk('r2')->put($path, $file->getContent(), [
                'visibility' => 'public',
                'ContentType' => $file->getMimeType(),
            ]);

            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_url'] = route('r2.serve', ['path' => $path]);
            $validated['has_file'] = true;
        }

        TenantClause::create(array_merge($validated, [
            'tenant_id' => Auth::user()->tenant_id,
        ]));

        return redirect()->route('settings.index')->with('success', 'Cláusula creada exitosamente.');
    }

    public function updateClause(Request $request, TenantClause $clause): RedirectResponse
    {
        if ($clause->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.index')->with('error', 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:terms,warranty,privacy,return,other',
            'is_active' => 'boolean',
            'print_on_receipt' => 'boolean',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['print_on_receipt'] = $request->boolean('print_on_receipt');

        if ($request->hasFile('file')) {
            if ($clause->file_path) {
                Storage::disk('r2')->delete($clause->file_path);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension() ?: 'pdf';
            $filename = Str::uuid() . '.' . $extension;
            $path = 'clauses/' . date('Y/m') . '/' . $filename;

            Storage::disk('r2')->put($path, $file->getContent(), [
                'visibility' => 'public',
                'ContentType' => $file->getMimeType(),
            ]);

            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_url'] = route('r2.serve', ['path' => $path]);
            $validated['has_file'] = true;
        }

        $clause->update($validated);

        return redirect()->route('settings.index')->with('success', 'Cláusula actualizada.');
    }

    public function deleteClause(TenantClause $clause): RedirectResponse
    {
        if ($clause->tenant_id !== Auth::user()->tenant_id) {
            return redirect()->route('settings.index')->with('error', 'Acceso no autorizado.');
        }

        if ($clause->file_path) {
            Storage::disk('r2')->delete($clause->file_path);
        }

        $clause->delete();

        return redirect()->route('settings.index')->with('success', 'Cláusula eliminada.');
    }

    public function index(): View
    {
        $tenant = Auth::user()->tenant;
        $users = User::where('tenant_id', $tenant->id)->with('roles')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $clauses = TenantClause::orderBy('sort_order')->get();
        $templates = NotificationTemplate::where('tenant_id', $tenant->id)->orderBy('event')->get();

        return view('settings.index', compact('tenant', 'users', 'roles', 'permissions', 'clauses', 'templates'));
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

        return redirect()->route('settings.index')->with('success', 'Configuración actualizada.');
    }

    public function updateNotificationTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event' => 'required|string',
            'channel' => 'required|in:email,whatsapp',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $tenantId = Auth::user()->tenant_id;

        NotificationTemplate::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'event' => $validated['event'],
                'channel' => $validated['channel'],
            ],
            [
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'is_active' => $validated['is_active'],
            ]
        );

        Log::info("Notification template updated: event={$validated['event']}, channel={$validated['channel']}");

        return redirect()->route('settings.index')->with('success', 'Plantilla de notificación actualizada.');
    }
}
