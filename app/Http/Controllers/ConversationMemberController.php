<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Conversation;

class ConversationMemberController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/conversations/{conversation}/members",
     *      summary="Add members to a group conversation",
     *      tags={"Conversation Members"},
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
     *              required={"member_ids"},
     *              @OA\Property(
     *                  property="member_ids",
     *                  type="array",
     *                  @OA\Items(type="integer", example=3)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Members added successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Members added successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Cannot add members to a one-to-one conversation"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function addMembers(Request $request, Conversation $conversation): JsonResponse
    {
        // Ensure this conversation is a group conversation
        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Cannot add members to a one-to-one conversation'], 403);
        }

        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        // Add new members to the conversation without removing existing ones
        $conversation->members()->syncWithoutDetaching($request->member_ids);

        return response()->json(['message' => 'Members added successfully']);
    }

    /**
     * @OA\Get(
     *      path="/api/conversations/{conversation}/members",
     *      summary="List all members of a conversation",
     *      tags={"Conversation Members"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="conversation",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="List of conversation members",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", example="john.doe@example.com")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation not found"
     *      )
     *  )
     *
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function listMembers(Conversation $conversation): JsonResponse
    {
        $members = $conversation->members()->get();
        return response()->json($members);
    }
}
