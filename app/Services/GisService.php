<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Region;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GisService
{
    public function reverseGeocode(float $latitude, float $longitude): string
    {
        $mapboxKey = (string) config('services.mapbox.key');
        if ($mapboxKey !== '') {
            try {
                $response = Http::timeout(8)->retry(2, 250)->get(
                    sprintf('https://api.mapbox.com/geocoding/v5/mapbox.places/%F,%F.json', $longitude, $latitude),
                    ['access_token' => $mapboxKey, 'language' => 'id', 'limit' => 1]
                )->throw();
                $placeName = data_get($response->json(), 'features.0.place_name');
                if (is_string($placeName) && $placeName !== '') {
                    return $placeName;
                }
            } catch (Throwable) {
            }
        }

        $googleKey = (string) config('services.google_maps.key');
        if ($googleKey !== '') {
            try {
                $response = Http::timeout(8)->retry(2, 250)->get(
                    'https://maps.googleapis.com/maps/api/geocode/json',
                    ['latlng' => sprintf('%F,%F', $latitude, $longitude), 'key' => $googleKey, 'language' => 'id']
                )->throw();
                $address = data_get($response->json(), 'results.0.formatted_address');
                if (is_string($address) && $address !== '') {
                    return $address;
                }
            } catch (Throwable) {
            }
        }

        return sprintf('Koordinat %.7f, %.7f', $latitude, $longitude);
    }

    public function detectDuplicate(float $latitude, float $longitude, ?int $exceptComplaintId = null, ?string $date = null): ?Complaint
    {
        $query = Complaint::query()
            ->whereDate('created_at', $date ?? now()->toDateString())
            ->where('status', '!=', Complaint::STATUS_REJECTED)
            ->whereRaw(
                "ST_Distance_Sphere(location, ST_GeomFromText(?, 4326, 'axis-order=long-lat')) <= ?",
                [Complaint::pointWkt($latitude, $longitude), (int) config('gis.duplicate_radius_meters', 15)]
            )->orderBy('created_at');
        if ($exceptComplaintId !== null) {
            $query->whereKeyNot($exceptComplaintId);
        }

        return $query->first();
    }

    public function assignRegionStub(float $latitude, float $longitude): ?Region
    {
        return Region::query()->where('is_active', true)
            ->whereRaw(
                "ST_Contains(boundary, ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
                [Complaint::pointWkt($latitude, $longitude)]
            )
            ->orderByRaw("FIELD(level, 'kelurahan', 'kecamatan', 'kota', 'kabupaten', 'provinsi')")
            ->first();
    }

    public function descendantRegionIds(?int $regionId): array
    {
        if (! $regionId) {
            return [];
        }
        $ids = [$regionId];
        $frontier = [$regionId];
        while ($frontier !== []) {
            $children = Region::query()->whereIn('parent_id', $frontier)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $children = array_values(array_diff($children, $ids));
            $ids = array_values(array_unique([...$ids, ...$children]));
            $frontier = $children;
        }

        return $ids;
    }

    public function complaintFeatureCollection(Collection $complaints): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $complaints->map(fn (Complaint $complaint): array => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$complaint->longitude, $complaint->latitude]],
                'properties' => [
                    'id' => $complaint->id,
                    'title' => $complaint->title,
                    'url' => route('complaints.show', $complaint),
                    'route_url' => $this->googleMapsDirectionsUrl((float) $complaint->latitude, (float) $complaint->longitude),
                    'category_label' => $complaint->category_label,
                    'color' => $complaint->category_color,
                    'status' => $complaint->status,
                    'address' => Str::limit($complaint->address_text, 180),
                    'region' => $complaint->region?->name,
                    'media_type' => $complaint->primary_media?->media_type,
                    'upvotes_count' => $complaint->upvotes_count,
                    'created_at' => $complaint->created_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                ],
            ])->values()->all(),
        ];
    }

    public function regionSummaryFeatureCollection(Collection $regions): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $regions->map(fn (Region $region): array => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$region->center_longitude, $region->center_latitude]],
                'properties' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'level' => $region->level,
                    'total' => (int) ($region->approved_complaints_count ?? 0),
                ],
            ])->values()->all(),
        ];
    }

    public function googleMapsDirectionsUrl(float $latitude, float $longitude): string
    {
        $destination = number_format($latitude, 7, '.', '').','.number_format($longitude, 7, '.', '');

        return 'https://www.google.com/maps/dir/?api=1&destination='.
            rawurlencode($destination).
            '&travelmode=driving';
    }

}
