<?php

namespace PK\Posts\Controllers;

use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\OpenApi\Schemas\PostCreated;
use PK\Posts\Services\PostFacade;

#[OA\Post(
    path: '/api/v2/post',
    operationId: 'createThread',
    summary: 'Создать новую нить',
    tags: ['post'],
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            required: [
                'tag',
                'message'
            ],
            properties: [
                new OA\Property(
                    property: 'tag',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'poster',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'subject',
                    type: 'string'
                )
            ]
        )
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
    'Необходимо передать tag'
)]
#[Error(
    404,
    'Не найдена доска с таким тегом',
    OutOfBoundsException::class,
    'Доска не найдена: rnd'
)]
final class CreateThread
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req): JsonResponse
    {
        if (!$req->getParams('tag')) {
            return (new JsonResponse([], 400))
                ->setException(new InvalidArgumentException("Не передан tag"));
        }

        if (!$req->getParams('message')) {
            return (new JsonResponse([], 400))
                ->setException(new InvalidArgumentException("Не передан message"));
        }

        $params = [];

        if ($req->getParams('poster')) {
            $params['poster'] = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $params['subject'] = $req->getParams('subject');
        }

        try {
            $data = $this->post_facade->createThread(
                $req->getParams('tag'),
                $req->getParams('message'),
                $params
            );
        } catch (OutOfBoundsException) {
            return (new JsonResponse([], 404))
                ->setException(new Exception('Нет доски с таким тегом'));
        }

        return new JsonResponse($data, 201);
    }
}
