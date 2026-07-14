<?php

namespace App\Livewire\Admin;

use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Laporan Masuk')]
class IncomingComplaints extends Component
{
    use WithPagination;

    public array $editDescription = [];
    public array $rejectReason = [];

    public function approve(int $complaintId, ComplaintService $service): void
    {
        $complaint = $this->findComplaint($complaintId);
        Gate::authorize('moderate', $complaint);
        $service->approve(auth()->user(), $complaint);
        session()->flash('success', 'Laporan disetujui dan dipublikasikan.');
    }

    public function editAndApprove(int $complaintId, ComplaintService $service): void
    {
        $complaint = $this->findComplaint($complaintId);
        Gate::authorize('moderate', $complaint);
        $description = trim((string) ($this->editDescription[$complaintId] ?? $complaint->description));
        validator(['description' => $description], ['description' => ['required', 'string', 'min:10', 'max:2000']])->validate();
        $service->approve(auth()->user(), $complaint, $description);
        unset($this->editDescription[$complaintId]);
        session()->flash('success', 'Redaksi diperbarui dan laporan disetujui.');
    }

    public function reject(int $complaintId, ComplaintService $service): void
    {
        $complaint = $this->findComplaint($complaintId);
        Gate::authorize('moderate', $complaint);
        $reason = trim((string) ($this->rejectReason[$complaintId] ?? ''));
        validator(['reason' => $reason], ['reason' => ['required', 'string', 'min:5', 'max:1000']])->validate();
        $service->reject(auth()->user(), $complaint, $reason);
        unset($this->rejectReason[$complaintId]);
        session()->flash('success', 'Laporan ditolak. Pelapor tetap dapat memperbaiki dan mengirim ulang.');
    }

    private function findComplaint(int $id): Complaint
    {
        return Complaint::query()->forAdmin(auth()->user())->where('status', Complaint::STATUS_PENDING)->findOrFail($id);
    }

    public function render()
    {
        $complaints = Complaint::query()->forAdmin(auth()->user())
            ->where('status', Complaint::STATUS_PENDING)
            ->with(['user', 'region', 'duplicateOf', 'categoryRecord', 'media'])
            ->oldest()->paginate(10);
        foreach ($complaints as $complaint) {
            $this->editDescription[$complaint->id] ??= $complaint->description;
        }

        return view('livewire.admin.incoming-complaints', compact('complaints'));
    }
}
