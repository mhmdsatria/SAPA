<?php

namespace App\Livewire\Public;

use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.citizen')]
#[Title('Dashboard Pelapor')]
class ProfileDashboard extends Component
{
    use WithPagination;

    public string $status = '';
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteComplaint(int $id, ComplaintService $service): void
    {
        $complaint = Complaint::query()->where('user_id', auth()->id())->with('media')->findOrFail($id);
        Gate::authorize('delete', $complaint);
        $service->deleteReporterComplaint($complaint);
        session()->flash('success', 'Laporan yang belum disetujui berhasil dihapus.');
    }

    public function render()
    {
        $base = Complaint::query()->where('user_id', auth()->id());
        $counts = [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', Complaint::STATUS_PENDING)->count(),
            'approved' => (clone $base)->where('status', Complaint::STATUS_APPROVED)->count(),
            'rejected' => (clone $base)->where('status', Complaint::STATUS_REJECTED)->count(),
        ];
        $complaints = Complaint::query()->where('user_id', auth()->id())
            ->with(['region', 'categoryRecord', 'media'])
            ->when($this->status !== '', fn (Builder $q): Builder => $q->where('status', $this->status))
            ->when(trim($this->search) !== '', function (Builder $q): Builder {
                $term = '%'.trim($this->search).'%';
                return $q->where(fn (Builder $n): Builder => $n->where('title', 'like', $term)->orWhere('address_text', 'like', $term));
            })->latest()->paginate(10);

        return view('livewire.public.profile-dashboard', compact('counts', 'complaints'));
    }
}
