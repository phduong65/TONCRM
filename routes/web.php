<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\ChatSimulatorController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inbox
    Route::get('/conversations', [ConversationController::class, 'index'])
        ->name('conversations.index')->middleware('can:view-conversations');

    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])
        ->name('conversations.show')->middleware('can:view-conversations');

    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])
        ->name('messages.store')->middleware('can:reply-conversations');

    Route::post('/conversations/{conversation}/toggle-ai', [ConversationController::class, 'toggleAi'])
        ->name('conversations.toggle-ai')->middleware('can:reply-conversations');

    Route::post('/conversations/{conversation}/assign', [ConversationController::class, 'assign'])
        ->name('conversations.assign')->middleware('can:assign-conversations');

    Route::post('/conversations/{conversation}/close', [ConversationController::class, 'close'])
        ->name('conversations.close')->middleware('can:reply-conversations');

    Route::post('/conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])
        ->name('conversations.reopen')->middleware('can:reply-conversations');

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index'])
        ->name('contacts.index')->middleware('can:view-contacts');

    Route::post('/contacts', [ContactController::class, 'store'])
        ->name('contacts.store')->middleware('can:create-contacts');

    Route::put('/contacts/{contact}', [ContactController::class, 'update'])
        ->name('contacts.update')->middleware('can:edit-contacts');

    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
        ->name('contacts.destroy')->middleware('can:delete-contacts');

    // Channels
    Route::get('/channels', [ChannelController::class, 'index'])
        ->name('channels.index')->middleware('can:view-channels');

    Route::post('/channels', [ChannelController::class, 'store'])
        ->name('channels.store')->middleware('can:create-channels');

    Route::put('/channels/{channel}', [ChannelController::class, 'update'])
        ->name('channels.update')->middleware('can:edit-channels');

    Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])
        ->name('channels.destroy')->middleware('can:delete-channels');

    // Knowledge Base
    Route::get('/knowledge-bases', [KnowledgeBaseController::class, 'index'])
        ->name('knowledge-bases.index')->middleware('can:view-knowledge-bases');

    Route::post('/knowledge-bases', [KnowledgeBaseController::class, 'store'])
        ->name('knowledge-bases.store')->middleware('can:create-knowledge-bases');

    Route::put('/knowledge-bases/{knowledgeBase}', [KnowledgeBaseController::class, 'update'])
        ->name('knowledge-bases.update')->middleware('can:edit-knowledge-bases');

    Route::delete('/knowledge-bases/{knowledgeBase}', [KnowledgeBaseController::class, 'destroy'])
        ->name('knowledge-bases.destroy')->middleware('can:delete-knowledge-bases');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')->middleware('can:view-reports');

    Route::get('/reports/export', [ReportController::class, 'export'])
        ->name('reports.export')->middleware('can:export-reports');

    // Settings & Users
    Route::middleware('can:manage-settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('can:manage-users')->prefix('settings/users')->name('settings.users.')->group(function () {
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Dev tools — chỉ hoạt động khi APP_DEBUG=true
    if (config('app.debug')) {
        Route::prefix('dev')->name('dev.')->group(function () {
            Route::get('/chat', [ChatSimulatorController::class, 'index'])->name('chat');
            Route::post('/chat/send', [ChatSimulatorController::class, 'send'])->name('chat.send');
        });
    }
});
