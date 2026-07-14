<?php

return [
    'default_latitude' => (float) env('GIS_DEFAULT_LATITUDE', -6.917464),
    'default_longitude' => (float) env('GIS_DEFAULT_LONGITUDE', 107.619123),
    'default_zoom' => (int) env('GIS_DEFAULT_ZOOM', 12),
    'duplicate_radius_meters' => (int) env('GIS_DUPLICATE_RADIUS_METERS', 15),
    'marker_zoom_threshold' => 12,
    'category_colors' => [
        'jalan' => '#dc2626',
        'kebersihan' => '#16a34a',
        'penerangan' => '#eab308',
        'lainnya' => '#2563eb',
    ],
];
