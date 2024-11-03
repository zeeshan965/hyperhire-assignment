<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationMemberController;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    // Conversation Routes
    Route::prefix('conversations')->group(function () {

        // Create or fetch a one-to-one conversation between two users
        Route::post('/one-to-one', [ConversationController::class, 'createOrFetchOneToOneConversation']);

        // Create a new group conversation
        Route::post('/group', [ConversationController::class, 'createGroupConversation']);

        // List all conversations for the authenticated user
        Route::get('/', [ConversationController::class, 'listUserConversations']);

        // Retrieve conversation details (both one-to-one and group)
        Route::get('/{conversation}', [ConversationController::class, 'showConversation']);

        // Leave a conversation
        Route::post('/{conversation}/leave', [ConversationController::class, 'leaveConversation']);

        // Add members to a group conversation (only for group chats)
        Route::post('/{conversation}/members', [ConversationMemberController::class, 'addMembers']);

        // List members of a conversation (only for group chats)
        Route::get('/{conversation}/members', [ConversationMemberController::class, 'listMembers']);
    });

    // Message Routes
    Route::prefix('conversations/{conversation}/messages')->group(function () {

        // Send a message in a conversation (both one-to-one and group)
        Route::post('/', [MessageController::class, 'sendMessage']);

        // List all messages in a conversation
        Route::get('/', [MessageController::class, 'listMessages']);
    });

});
