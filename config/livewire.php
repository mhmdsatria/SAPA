<?php

return [
    'component_locations' => [
        resource_path('views/components'),
        resource_path('views/livewire'),
    ],

    'component_namespaces' => [
        'layouts' => resource_path('views/layouts'),
        'pages' => resource_path('views/pages'),
    ],

    'component_layout' => 'layouts::app',
    'component_placeholder' => null,

    'make_command' => [
        'type' => 'sfc',
        'emoji' => true,
        'with' => [
            'js' => false,
            'css' => false,
            'test' => false,
        ],
    ],

    'class_namespace' => 'App\\Livewire',
    'class_path' => app_path('Livewire'),
    'view_path' => resource_path('views/livewire'),

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK'),
        'rules' => ['required', 'file', 'max:51200'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 10,
        'cleanup' => true,
        'chunking' => true,
        'chunk_size' => 1024 * 1024,
        'chunk_threshold' => 1024 * 1024,
    ],

    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2563eb',
    ],

    'inject_morph_markers' => true,
    'smart_wire_keys' => true,
    'pagination_theme' => 'tailwind',
    'release_token' => 'laporkota-gis-v2',
    'csp_safe' => false,

    'payload' => [
        'max_size' => 2 * 1024 * 1024,
        'max_nesting_depth' => 12,
        'max_calls' => 60,
        'max_components' => 250,
    ],
];
