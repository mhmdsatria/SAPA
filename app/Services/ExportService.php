<?php

namespace App\Services;

use App\Exports\ComplaintsExport;
use App\Models\Complaint;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    public function __construct(private readonly GisService $gisService)
    {
    }

    public function query(User $admin, array $filters): Builder
    {
        $regionIds = $this->gisService->descendantRegionIds(isset($filters['region_id']) ? (int) $filters['region_id'] : null);

        return Complaint::query()->forAdmin($admin)
            ->with(['user', 'region', 'moderator', 'categoryRecord', 'media'])
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $id): Builder => $query->where('category_id', $id))
            ->when($regionIds !== [], fn (Builder $query): Builder => $query->whereIn('region_id', $regionIds))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->latest();
    }

    public function excel(User $admin, array $filters): mixed
    {
        return Excel::download(new ComplaintsExport($this->query($admin, $filters)->get()), 'rekap-keluhan-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pdf(User $admin, array $filters): mixed
    {
        return Pdf::loadView('exports.complaints-pdf', ['complaints' => $this->query($admin, $filters)->get(), 'filters' => $filters])
            ->setPaper('a4', 'landscape')->download('rekap-keluhan-'.now()->format('Ymd-His').'.pdf');
    }
}
