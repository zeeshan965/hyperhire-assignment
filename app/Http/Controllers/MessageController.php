<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Mail\Events\MessageSent;

class MessageController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/conversations/{conversation}/messages",
     *      summary="Send a message in a conversation",
     *      tags={"Messages"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="conversation",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="content", type="string", example="Hello, world!"),
     *              @OA\Property(property="attachment", type="string", format="binary")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Message sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="user_id", type="integer", example=1),
     *              @OA\Property(property="content", type="string", example="Hello, world!"),
     *              @OA\Property(property="attachment", type="string", example="attachments/file.jpg"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:34:56Z")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'content' => 'required_without:attachment|string',
            'attachment' => 'nullable|file',
        ]);

        // Determine the storage path based on MIME type
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mimeType = $file->getMimeType();

            // Determine the directory based on file type
            $directory = str_starts_with($mimeType, 'image/') ? 'root/picture' : (str_starts_with($mimeType, 'video/') ? 'root/video' : 'root/other');
            
            // Store the file in the determined directory
            $attachmentPath = $file->store($directory, 'public');
        }
    
        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'attachment' => $attachmentPath,
        ]);
    
        // Broadcast message to the chatroom channel
        broadcast(new \App\Events\MessageSent($message))->toOthers();
    
        return response()->json($message);
    }

    /**
     * @OA\Get(
     *      path="/api/conversations/{conversation}/messages",
     *      summary="List all messages in a conversation",
     *      tags={"Messages"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="conversation",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="List of messages",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="content", type="string", example="Hello, world!"),
     *                  @OA\Property(property="attachment", type="string", nullable=true, example="attachments/file.jpg"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:34:56Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function listMessages(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        return response()->json($messages);
    }
}
