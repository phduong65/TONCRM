<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $contacts = Contact::where('tenant_id', $tenantId)
            ->when($request->filled('search'), fn($q) =>
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'ilike', '%' . $request->search . '%');
                })
            )
            ->withCount('conversations')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('contacts.index', compact('contacts'));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        activity()->causedBy(auth()->user())->performedOn($contact)->log('created');

        return back()->with('success', 'Đã tạo liên hệ thành công.');
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        abort_unless($contact->tenant_id === auth()->user()->tenant_id, 403);

        $contact->update($request->validated());
        activity()->causedBy(auth()->user())->performedOn($contact)->log('updated');

        return back()->with('success', 'Đã cập nhật liên hệ.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        abort_unless($contact->tenant_id === auth()->user()->tenant_id, 403);

        activity()->causedBy(auth()->user())->performedOn($contact)->log('deleted');
        $contact->delete();

        return back()->with('success', 'Đã xóa liên hệ.');
    }
}
