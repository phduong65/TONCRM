<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'tenant_id' => auth()->user()->tenant_id,
            'is_active' => true,
        ]);

        $user->syncRoles([$request->role]);
        activity()->causedBy(auth()->user())->performedOn($user)->log('created');

        return redirect()->route('settings.index')->with('success', 'Đã tạo người dùng thành công.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->tenant_id === auth()->user()->tenant_id, 403);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        activity()->causedBy(auth()->user())->performedOn($user)->log('updated');

        return redirect()->route('settings.index')->with('success', 'Đã cập nhật người dùng.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->tenant_id === auth()->user()->tenant_id, 403);
        abort_if($user->id === auth()->id(), 403, 'Không thể xóa tài khoản đang dùng.');

        activity()->causedBy(auth()->user())->performedOn($user)->log('deleted');
        $user->delete();

        return redirect()->route('settings.index')->with('success', 'Đã xóa người dùng.');
    }
}
