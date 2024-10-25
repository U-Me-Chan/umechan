<?php

namespace PK\Posts\Controllers;

use OpenApi\Attributes as OA;

use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\IPostRepository;
use PK\Posts\Post\Post;
use PK\Posts\ResponseSchemas\ThreadResponseSchema;

#[OA\Get(path: '/api/v2/post/{id}', parameters: [
    new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
])]
#[OA\Response(
    response: 200,
    description: 'Return thread data',
    content: new OA\JsonContent(ref: '#/components/schemas/Thread')
)]
final class GetThread
{
    public function __construct(
        private IPostRepository $post_repo
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $id = $vars['id'];

        try {
            /** @var Post */
            $thread = $this->post_repo->findOne(['id' => $id]);
        } catch (\OutOfBoundsException) {
            return new Response([], 404);
        }

        return new Response(new ThreadResponseSchema($thread));
    }
}
