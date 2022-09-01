<?php

use Benjacho\BelongsToManyField\Http\Controllers\ResourceController;

Route::get('/{resource}/options/{relationship}/{optionsLabel}/{dependsOnValue?}/{dependsOnKey?}', [ResourceController::class, 'index']);

Route::post('quickCreate', [ResourceController::class, 'create'])
        ->middleware(['can:manage content']);
