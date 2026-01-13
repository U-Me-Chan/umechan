<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use OutOfBoundsException;
use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\Exceptions\InvalidPostPasswordException;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\Services\PostFacade;
use RuntimeException;

#[OA\Delete(
    path: '/api/v2/post/{id}',
    operationId: 'deletePost',
    summary: 'Удаляет пост',
    tags: ['post'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Идентификатор поста',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        )
    ],
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            required: [
                'password'
            ],
            properties: [
                new OA\Property(
                    property: 'password',
                    type: 'string'
                )
            ]
        )
    )
)]
#[Response(
    204,
    'Пост успешно удалён'
)]
#[Error(
    400,
    'Ошибка разбора параметров запроса',
    InvalidArgumentException::class,
    'Укажите пароль для удаления поста'
)]
#[Error(
    404,
    'Пост не найден',
    ThreadNotFoundException::class,
    'Пост не найден'
)]
#[Error(
    401,
    'Неверный пароль',
    InvalidPostPasswordException::class
)]
class PostDeleter
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        if (!$req->getParams('password')) {
            return (new JsonResponse([], 400))
                ->setException(new InvalidArgumentException('Укажите пароль для удаления поста'));
        }

        try {
            $this->post_facade->deletePostByAuthor($vars['id'], $req->getParams('password'));
        } catch (ThreadNotFoundException $e) {
            return (new JsonResponse([], 404))->setException($e);
        } catch (InvalidPostPasswordException $e) {
            return (new JsonResponse([], 403))->setException($e);
        }

        return new JsonResponse([], 204);
    }
}
