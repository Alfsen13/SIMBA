<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

Route::middleware([ForceJsonResponse::class])->group(function () {
    Route::get('materi/{id}/file', function ($id) {
        return response()->json(['message' => 'File serve endpoint (placeholder)']);
    });
});
