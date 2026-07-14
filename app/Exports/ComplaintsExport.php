<?php

namespace App\Exports;

use App\Models\Complaint;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplaintsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly Collection $complaints)
    {
    }

    public function collection(): Collection
    {
        return $this->complaints;
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal', 'Judul', 'Kategori', 'Status', 'Pelapor', 'Anonim',
            'Alamat', 'Latitude', 'Longitude', 'Akurasi GPS', 'Foto Diambil',
            'Foto Lebih 7 Hari', 'Duplikat Spasial', 'Upvote', 'Komentar',
        ];
    }

    public function map($row): array
    {
        /** @var Complaint $row */
        return [
            $row->id,
            $row->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            $row->title,
            $row->category_label,
            ucfirst($row->status),
            $row->reporter_name,
            $row->is_anonymous ? 'Ya' : 'Tidak',
            $row->address_text,
            $row->latitude,
            $row->longitude,
            $row->gps_accuracy,
            $row->image_taken_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            $row->exif_is_stale ? 'Ya' : 'Tidak',
            $row->is_duplicate_flag ? 'Ya' : 'Tidak',
            $row->upvotes_count,
            $row->comments_count,
        ];
    }
}
