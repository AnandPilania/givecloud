<?php

use Ds\Domain\Flatfile\Controllers\TokenController;
use Ds\Domain\Flatfile\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('flatfile/webhook/contributions', [WebhookController::class, 'contributions'])->name('flatfile.webhook.contributions');
Route::post('flatfile/webhook/supporters', [WebhookController::class, 'supporters'])->name('flatfile.webhook.supporters');
Route::post('flatfile/webhook/sponsorships', [WebhookController::class, 'sponsorships'])->name('flatfile.webhook.sponsorships');

Route::get('flatfile/token/sponsorships', [TokenController::class, 'sponsorships'])->name('flatfile.token.sponsorships');
