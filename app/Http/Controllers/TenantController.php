<?php

namespace App\Http\Controllers;

use App\Mail\NewTenantNotification;
use App\Mail\VerifyEmail;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string|max:50',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        return DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'slug' => Str::slug($validated['business_name']) . '-' . Str::random(4),
                'phone' => $validated['business_phone'],
                'email' => $validated['admin_email'],
                'is_active' => true,
                'plan_id' => Plan::where('slug', 'premium')->first()->id,
                'trial_ends_at' => now()->addDays(7),
                'subscription_status' => 'trial',
            ]);

            $user = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verification_token' => Str::random(60),
            ]);

            $user->assignRole('admin_tenant');

            try {
                Mail::to($user->email)->send(new VerifyEmail($user, $tenant));
            } catch (\Throwable $e) {
                logger()->error('Error al encolar correo de verificación', ['error' => $e->getMessage(), 'user' => $user->id]);
            }

            try {
                $superadmins = User::where('is_superadmin', true)->get();
                foreach ($superadmins as $superadmin) {
                    Mail::to($superadmin->email)->send(new NewTenantNotification($tenant, $user));
                }
            } catch (\Throwable $e) {
                logger()->error('Error encolando notificación a superadmin', ['error' => $e->getMessage(), 'tenant' => $tenant->id]);
            }

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', "¡Bienvenido a RepCellPOS! Tu empresa '{$tenant->name}' ha sido registrada exitosamente.");
        });
    }
}
