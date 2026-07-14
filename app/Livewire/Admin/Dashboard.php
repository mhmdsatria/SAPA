<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render()
    {
        $query = Complaint::query()->forAdmin(auth()->user());
        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', Complaint::STATUS_PENDING)->count(),
            'approved' => (clone $query)->where('status', Complaint::STATUS_APPROVED)->count(),
            'rejected' => (clone $query)->where('status', Complaint::STATUS_REJECTED)->count(),
            'stale' => (clone $query)->where('exif_is_stale', true)->count(),
            'duplicate' => (clone $query)->where('is_duplicate_flag', true)->count(),
        ];
        $categoryStats = Category::query()->withCount(['complaints' => fn ($q) => $q->forAdmin(auth()->user())])
            ->orderBy('sort_order')->get();
        $dailyStats = Complaint::query()->forAdmin(auth()->user())
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) AS report_date, COUNT(*) AS total')
            ->groupBy('report_date')->orderBy('report_date')->get();
        $recent = Complaint::query()->forAdmin(auth()->user())
            ->with(['user', 'categoryRecord', 'media'])->latest()->limit(6)->get();

        return view('livewire.admin.dashboard', compact('stats', 'categoryStats', 'dailyStats', 'recent'));
    }
}
