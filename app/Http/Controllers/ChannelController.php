<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(): View
    {
        $channels = Channel::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('conversations')
            ->orderByDesc('created_at')
            ->get();

        return view('channels.index', compact('channels'));
    }

    public function store(StoreChannelRequest $request): RedirectResponse
    {
        $channel = Channel::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        activity()->causedBy(auth()->user())->performedOn($channel)->log('created');

        return back()->with('success', 'Đã thêm kênh thành công.');
    }

    public function update(UpdateChannelRequest $request, Channel $channel): RedirectResponse
    {
        abort_unless($channel->tenant_id === auth()->user()->tenant_id, 403);

        $channel->update($request->validated());
        activity()->causedBy(auth()->user())->performedOn($channel)->log('updated');

        return back()->with('success', 'Đã cập nhật kênh.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        abort_unless($channel->tenant_id === auth()->user()->tenant_id, 403);

        activity()->causedBy(auth()->user())->performedOn($channel)->log('deleted');
        $channel->delete();

        return back()->with('success', 'Đã xóa kênh.');
    }
}
