<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api_download',
        'api_upload',
        'rename',
        'delete',
        'refresh',
        'UploadString',
        'TaskUpload',
        'TaskDownload',

    ];
}
