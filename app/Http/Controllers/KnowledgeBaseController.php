<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnowledgeBaseRequest;
use App\Http\Requests\UpdateKnowledgeBaseRequest;
use App\Jobs\GenerateEmbeddingJob;
use App\Models\KnowledgeBase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $entries = KnowledgeBase::where('tenant_id', $tenantId)
            ->when($request->filled('search'), fn($q) =>
                $q->where('title', 'ilike', '%' . $request->search . '%')
                  ->orWhere('content', 'ilike', '%' . $request->search . '%')
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('knowledge-bases.index', compact('entries'));
    }

    public function store(StoreKnowledgeBaseRequest $request): RedirectResponse
    {
        $entry = KnowledgeBase::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        dispatch(new GenerateEmbeddingJob($entry))->onQueue('default');

        activity()->causedBy(auth()->user())->performedOn($entry)->log('created');

        return back()->with('success', 'Đã thêm nội dung. Đang tạo embedding...');
    }

    public function update(UpdateKnowledgeBaseRequest $request, KnowledgeBase $knowledgeBase): RedirectResponse
    {
        abort_unless($knowledgeBase->tenant_id === auth()->user()->tenant_id, 403);

        $knowledgeBase->update($request->validated());
        dispatch(new GenerateEmbeddingJob($knowledgeBase))->onQueue('default');

        activity()->causedBy(auth()->user())->performedOn($knowledgeBase)->log('updated');

        return back()->with('success', 'Đã cập nhật. Đang tạo lại embedding...');
    }

    public function destroy(KnowledgeBase $knowledgeBase): RedirectResponse
    {
        abort_unless($knowledgeBase->tenant_id === auth()->user()->tenant_id, 403);

        activity()->causedBy(auth()->user())->performedOn($knowledgeBase)->log('deleted');
        $knowledgeBase->delete();

        return back()->with('success', 'Đã xóa nội dung.');
    }
}
