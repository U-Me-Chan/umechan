<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post\Post;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v2/post/{id}',
    parameters: [
        new OA\Parameter(
            required: true,
            name: 'id',
            description: 'Идентификатор нити',
            in: 'path',
            allowEmptyValue: false,
            schema: new OA\Schema(
                type: 'integer'
            )
        )
    ]
)]

#[OA\Response(
    response: 200,
    description: 'Успешный ответ',
    content: new OA\JsonContent(
        title: 'Схема ответа',
        properties: [
            new OA\Property(
                property: 'payload',
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'thread_data',
                        type: 'object',
                        ref: '#/components/schemas/Post'
                    )
                ]
            ),
            new OA\Property(
                property: 'error',
                type: 'object',
                nullable: true
            )
        ]
    )
)]

#[OA\Response(
    response: 404,
    description: 'Ответ при отсутствии запрашиваемой нити'
)]
final class GetThread
{
    public function __construct(
        private PostStorage $storage
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $id = $vars['id'];

        try {
            /** @var Post */
            $post = $this->storage->findById($id);
        } catch (\OutOfBoundsException) {
            return new Response([], 404);
        }

        return new Response(['thread_data' => $post]);
    }
}
