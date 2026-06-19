<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::any('/facebook',  [WebhookController::class, 'facebook'])->name('facebook');
    Route::any('/instagram', [WebhookController::class, 'instagram'])->name('instagram');
    Route::any('/zalo',      [WebhookController::class, 'zalo'])->name('zalo');
    Route::post('/webchat',  [WebhookController::class, 'webchat'])->name('webchat');
});
