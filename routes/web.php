<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExternalApiKeyController;
use App\Http\Controllers\ExternalChatController;
use App\Http\Controllers\PublicChatController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/documents',
    [DocumentController::class, 'index']
)
    ->name('documents.index');

Route::post(
    '/documents',
    [DocumentController::class, 'store']
)
    ->name('documents.store');

Route::delete(
    '/documents/{document}',
    [DocumentController::class, 'destroy']
)
    ->name('documents.destroy');

Route::get(
    '/websites',
    [WebsiteController::class, 'index']
)
    ->name('websites.index');

Route::post(
    '/websites',
    [WebsiteController::class, 'store']
)
    ->name('websites.store');

Route::delete(
    '/websites/{websiteSource}',
    [WebsiteController::class, 'destroy']
)
    ->name('websites.destroy');

Route::post(
    '/chat',
    [ChatController::class, 'ask']
)
    ->name('chat.ask');

Route::get(
    '/api-keys',
    [ExternalApiKeyController::class, 'index']
)
    ->name('api-keys.index');

Route::post(
    '/api-keys',
    [ExternalApiKeyController::class, 'store']
)
    ->name('api-keys.store');

Route::patch(
    '/api-keys/{externalApiKey}/toggle',
    [ExternalApiKeyController::class, 'toggle']
)
    ->name('api-keys.toggle');

Route::delete(
    '/api-keys/{externalApiKey}',
    [ExternalApiKeyController::class, 'destroy']
)
    ->name('api-keys.destroy');

Route::post(
    '/api/external/chat',
    [ExternalChatController::class, 'ask']
)
    ->withoutMiddleware([
        ValidateCsrfToken::class,
    ])
    ->name('external.chat.ask');

Route::get(
    '/chatbot/public',
    [PublicChatController::class, 'show']
)
    ->name('public.chat.show');

Route::post(
    '/chatbot/public/ask',
    [PublicChatController::class, 'ask']
)
    ->name('public.chat.ask');