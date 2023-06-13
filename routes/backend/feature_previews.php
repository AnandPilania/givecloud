<?php

use Ds\Domain\FeaturePreviews\Http\Controllers\EnabledFeatureController;
use Ds\Domain\FeaturePreviews\Http\Controllers\FeatureStatesController;
use Illuminate\Support\Facades\Route;

Route::get('feature-previews', [FeatureStatesController::class, 'index'])->name('feature_previews.index');
Route::post('feature-previews/{feature}', [EnabledFeatureController::class, 'store'])->name('feature_previews.store');
Route::delete('feature-previews/{feature}', [EnabledFeatureController::class, 'destroy'])->name('feature_previews.destroy');
