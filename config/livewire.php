<?php

return [
    'class_namespace' => 'App\\Livewire',

    'layout' => 'components.layouts.app',

    'view_path' => resource_path('views/livewire'),

    'temporary_file_upload' => [
        // Keep Livewire temporary uploads on the app server so multiple uploads work,
        // even when the default filesystem uses S3.
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'public'),
        'directory' => null,
        'rules' => null,
        'middleware' => null,
    ],

    'asset_url' => env('LIVEWIRE_ASSET_URL'),

    'middleware_group' => 'web',
];

