<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private readonly ExportService $exportService)
    {
    }

    public function excel(Request $request): mixed
    {
        return $this->exportService->excel($request->user(), $this->validatedFilters($request));
    }

    public function pdf(Request $request): mixed
    {
        return $this->exportService->pdf($request->user(), $this->validatedFilters($request));
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);
    }
}
