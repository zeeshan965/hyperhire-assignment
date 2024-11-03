<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Conversation;

class ConversationController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/conversations/one-to-one",
     *      summary="Create or fetch a one-to-one conversation between two users",
     *      tags={"Conversations"},
     *     security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_one_id", "user_two_id"},
     *              @OA\Property(property="user_one_id", type="integer", example=1),
     *              @OA\Property(property="user_two_id", type="integer", example=2)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Conversation fetched or created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Conversation")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrFetchOneToOneConversation(Request $request): JsonResponse
    {
        $request->validate([
            'user_one_id' => 'required|exists:users,id',
            'user_two_id' => 'required|exists:users,id',
        ]);

        // Ensure consistent ordering of user IDs
        $userIds = [$request->user_one_id, $request->user_two_id];
        sort($userIds);

        // Check if the conversation already exists
        $conversation = Conversation::where('type', 'one-to-one')
            ->whereHas('members', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            }, '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'one-to-one',
            ]);
            $conversation->members()->sync($userIds);
        }

        return response()->json($conversation);
    }

    /**
     * @OA\Post(
     *      path="/api/conversations/group",
     *      summary="Create a new group conversation",
     *      tags={"Conversations"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "member_ids"},
     *              @OA\Property(property="name", type="string", example="Group Chat Name"),
     *              @OA\Property(
     *                  property="member_ids",
     *                  type="array",
     *                  @OA\Items(type="integer", example=3)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Group conversation created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Conversation")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function createGroupConversation(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $request->name,
        ]);

        $conversation->members()->sync($request->member_ids);

        return response()->json($conversation);
    }

    /**
     * @OA\Get(
     *      path="/api/conversations",
     *      summary="List all conversations for the authenticated user",
     *      tags={"Conversations"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Response(
     *          response=200,
     *          description="List of user conversations",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Conversation")
     *          )
     *      )
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function listUserConversations(Request $request): JsonResponse
    {
        $conversations = $request->user()->conversations()->with('members')->get();
        return response()->json($conversations);
    }

    /**
     * @OA\Get(
     *      path="/api/conversations/{conversation}",
     *      summary="Retrieve conversation details",
     *      tags={"Conversations"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="conversation",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Conversation details",
     *          @OA\JsonContent(ref="#/components/schemas/Conversation")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function showConversation(Conversation $conversation): JsonResponse
    {
        return response()->json($conversation->load('members'));
    }

    /**
     * @OA\Post(
     *      path="/api/conversations/{conversation}/leave",
     *      summary="Leave a conversation",
     *      tags={"Conversations"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="conversation",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully left the conversation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully left the conversation")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     * @param Conversation $conversation
     * @param Request $request
     * @return JsonResponse
     */
    public function leaveConversation(Conversation $conversation, Request $request): JsonResponse
    {
        $conversation->members()->detach($request->user()->id);
        return response()->json(['message' => 'Successfully left the conversation']);
    }
}

