<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Region;
use App\Services\GisService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GisController extends Controller
{
    public function __construct(private readonly GisService $gisService)
    {
    }

    public function complaints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'status' => ['nullable', Rule::in([Complaint::STATUS_PENDING, Complaint::STATUS_APPROVED, Complaint::STATUS_REJECTED])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'include_all' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $administrative = $request->boolean('include_all') && $user?->isAdmin();
        $query = Complaint::query()->with(['region', 'categoryRecord', 'media']);
        $administrative ? $query->forAdmin($user) : $query->published();

        $regionIds = $this->gisService->descendantRegionIds(isset($validated['region_id']) ? (int) $validated['region_id'] : null);
        $complaints = $query
            ->when($validated['category_id'] ?? null, fn (Builder $q, int $id): Builder => $q->where('category_id', $id))
            ->when($regionIds !== [], fn (Builder $q): Builder => $q->whereIn('region_id', $regionIds))
            ->when($administrative && ($validated['status'] ?? null), fn (Builder $q, string $status): Builder => $q->where('status', $status))
            ->when($validated['date_from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date))
            ->latest()->limit(3000)->get();

        return response()->json($this->gisService->complaintFeatureCollection($complaints));
    }

    public function regions(Request $request): JsonResponse
    {
        $validated = $request->validate(['category_id' => ['nullable', 'integer', 'exists:categories,id']]);
        $categoryId = $validated['category_id'] ?? null;
        $regions = Region::query()->where('is_active', true)
            ->whereIn('level', ['provinsi', 'kota', 'kabupaten'])
            ->withCount(['complaints as approved_complaints_count' => function (Builder $query) use ($categoryId): void {
                $query->published()->when($categoryId, fn (Builder $q, int $id): Builder => $q->where('category_id', $id));
            }])->get();

        return response()->json($this->gisService->regionSummaryFeatureCollection($regions));
    }

    public function reverseGeocode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'address' => $this->gisService->reverseGeocode((float) $data['latitude'], (float) $data['longitude']),
        ]);
    }
}
