<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $latitude = fake()->latitude(-7.05, -6.75);
        $longitude = fake()->longitude(107.45, 107.85);
        $delta = 0.03;
        $geojson = [
            'type' => 'Polygon',
            'coordinates' => [[
                [$longitude - $delta, $latitude - $delta],
                [$longitude + $delta, $latitude - $delta],
                [$longitude + $delta, $latitude + $delta],
                [$longitude - $delta, $latitude + $delta],
                [$longitude - $delta, $latitude - $delta],
            ]],
        ];
        $wkt = sprintf(
            'POLYGON((%1$f %2$f,%3$f %2$f,%3$f %4$f,%1$f %4$f,%1$f %2$f))',
            $longitude - $delta,
            $latitude - $delta,
            $longitude + $delta,
            $latitude + $delta
        );

        return [
            'parent_id' => null,
            'name' => fake()->city(),
            'code' => fake()->unique()->bothify('REG-####-??'),
            'level' => 'kota',
            'center_latitude' => $latitude,
            'center_longitude' => $longitude,
            'boundary' => DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')"),
            'boundary_geojson' => $geojson,
            'is_active' => true,
        ];
    }
}
