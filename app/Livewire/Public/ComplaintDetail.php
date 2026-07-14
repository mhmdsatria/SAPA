<?php

namespace App\Livewire\Public;

use App\Models\Comment;
use App\Models\Complaint;
use App\Services\CensorService;
use App\Services\ComplaintService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Keluhan')]
class ComplaintDetail extends Component
{
    public Complaint $complaint;
    public string $commentContent = '';
    public string $commentPreview = '';
    public bool $hasUpvoted = false;

    public function mount(Complaint $complaint): void
    {
        Gate::authorize('view', $complaint);
        $this->complaint = $complaint;
        $this->refreshState();
    }

    public function updatedCommentContent(CensorService $censorService): void
    {
        $this->commentPreview = $censorService->censor($this->commentContent);
    }

    public function addComment(ComplaintService $complaintService): mixed
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login', navigate: true);
        }
        $this->validate(['commentContent' => ['required', 'string', 'min:2', 'max:1000']]);
        $complaintService->addComment(auth()->user(), $this->complaint, $this->commentContent);
        $this->reset(['commentContent', 'commentPreview']);
        $this->refreshState();

        return null;
    }

    public function toggleUpvote(ComplaintService $complaintService): mixed
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login', navigate: true);
        }
        $this->hasUpvoted = $complaintService->toggleUpvote(auth()->user(), $this->complaint);
        $this->refreshState();

        return null;
    }

    public function toggleCommentVisibility(int $commentId, ComplaintService $complaintService): void
    {
        $comment = Comment::query()->findOrFail($commentId);
        Gate::authorize('hide', $comment);
        $complaintService->hideComment(auth()->user(), $comment, ! $comment->is_hidden);
        $this->refreshState();
    }

    private function refreshState(): void
    {
        $this->complaint = $this->complaint->fresh(['user', 'region', 'moderator', 'categoryRecord', 'media']) ?? $this->complaint;
        $this->hasUpvoted = auth()->check() && $this->complaint->upvotes()->where('user_id', auth()->id())->exists();
    }

    public function render()
    {
        $canModerate = auth()->check() && Gate::allows('moderate', $this->complaint);
        $comments = $this->complaint->comments()->with('user')
            ->when(! $canModerate, fn ($query) => $query->where('is_hidden', false))->oldest()->get();

        return view('livewire.public.complaint-detail', ['comments' => $comments]);
    }
}
