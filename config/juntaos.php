<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload_max_size' => env('JUNTAOS_UPLOAD_MAX_SIZE', 10240), // KB

    'allowed_document_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
    ],

    'allowed_attachment_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'text/plain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'default_pagination' => env('JUNTAOS_DEFAULT_PAGINATION', 15),

    /*
    |--------------------------------------------------------------------------
    | Export Limits
    |--------------------------------------------------------------------------
    */
    'export_limit' => env('JUNTAOS_EXPORT_LIMIT', 5000),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    | When enabled, certain destructive actions may be blocked.
    */
    'demo_mode' => env('JUNTAOS_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Organization Default Code
    |--------------------------------------------------------------------------
    */
    'organization_default_code' => env('JUNTAOS_ORG_DEFAULT_CODE', 'DEMO'),

];
