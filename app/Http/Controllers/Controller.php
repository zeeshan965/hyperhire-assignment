<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      title="API Documentation",
 *      version="1.0.0"
 *  )
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 *  )
 *
 * @OA\Components(
 *      @OA\Schema(
 *          schema="Conversation",
 *          type="object",
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="type", type="string", example="one-to-one"),
 *          @OA\Property(property="name", type="string", example="General Chat"),
 *          @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
 *          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:00:00Z")
 *      ),
 *      @OA\Schema(
 *          schema="User",
 *          type="object",
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="name", type="string", example="John Doe"),
 *          @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *          @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:00:00Z")
 *      )
 *  )
 */
abstract class Controller
{
    //
}
