<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasLocation
{
    public function scopeWithinRadius(
        Builder $query,
        float $latitude,
        float $longitude,
        float $radiusMeters
    ): Builder {
        $pointWkt = self::pointWkt($latitude, $longitude);

        return $query
            ->selectRaw(
                "*, ST_Distance_Sphere(location, ST_GeomFromText(?, 4326, 'axis-order=long-lat')) AS distance_meters",
                [$pointWkt]
            )
            ->whereRaw(
                "ST_Distance_Sphere(location, ST_GeomFromText(?, 4326, 'axis-order=long-lat')) <= ?",
                [$pointWkt, $radiusMeters]
            )
            ->orderBy('distance_meters');
    }

    public static function pointExpression(float $latitude, float $longitude): mixed
    {
        $wkt = self::pointWkt($latitude, $longitude);

        return DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')");
    }

    public static function pointWkt(float $latitude, float $longitude): string
    {
        $lat = number_format($latitude, 7, '.', '');
        $lng = number_format($longitude, 7, '.', '');

        return "POINT({$lng} {$lat})";
    }
}
