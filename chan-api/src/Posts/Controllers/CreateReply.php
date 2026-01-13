<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\Exceptions\ThreadBlockedException;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\OpenApi\Schemas\PostCreated;
use PK\Posts\Services\PostFacade;

#[OA\Put(
    path: '/api/v2/post/{id}',
    operationId: 'putReplyToThread',
    summary: 'Ответить в тред',
    tags: ['post'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            description: 'Идентификатор треда',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        )
    ],
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            required: [
                'message'
            ],
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'subject',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'poster',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'sage',
                    type: 'boolean',
                ),
            ]
        ),
    )
)]
#[Response(
    200,
    'Содержит идентификатор созданного поста и его пароль для удаления',
    payload_reference: PostCreated::class
)]
#[Error(
    400,
    'Ошибка разбора параметров запроса',
    InvalidArgumentException::class,
    'Необходимо передать message'
)]
#[Error(
    404,
    'Нет такой нити',
    ThreadNotFoundException::class
)]
final class CreateReply
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        $thread_id = $vars['id'];

        if (!$req->getParams('message')) {
            return (new JsonResponse([], 400))
                ->setException(new InvalidArgumentException('Необходимо передать message'));
        }

        $params = [];

        if ($req->getParams('poster')) {
            $params['poster'] = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $params['subject'] = $req->getParams('subject');
        }

        if ($req->getParams('sage')) {
            $params['sage'] = $req->getParams('sage');
        }

        try {
            $data = $this->post_facade->createReplyOnThread(
                $thread_id,
                $req->getParams('message'),
                $params
            );
        } catch (ThreadNotFoundException $e) {
            return new JsonResponse([], 404)->setException($e);
        } catch (ThreadBlockedException $e) {
            return new JsonResponse([], 403)->setException($e);
        }

        return new JsonResponse($data, 201);
    }
}
