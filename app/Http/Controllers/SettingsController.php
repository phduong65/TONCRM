<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $tenant = Tenant::findOrFail($tenantId);

        $users = User::where('tenant_id', $tenantId)
            ->with('roles')
            ->orderByDesc('created_at')
            ->get();

        $roles = Role::orderBy('name')->pluck('name');

        return view('settings.index', compact('tenant', 'users', 'roles'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant = Tenant::findOrFail(auth()->user()->tenant_id);
        $tenant->update(['name' => $request->name]);

        activity()->causedBy(auth()->user())->performedOn($tenant)->log('settings updated');

        return back()->with('success', 'Đã cập nhật cài đặt.');
    }
}
