<?php

namespace IH\Controllers;

use Medoo\Medoo;
use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use IH\Http\Response;
use IH\DTO\FileInfo as DTOFileInfo;
use IH\DTO\Error as DTOError;

class GetFile implements IController
{
    public function __construct(
        private string $static_url,
        private Medoo $db
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        if (!is_file(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . "{$vars['id']}")) {
            return new Response(new DTOError('not found'), Response::HTTP_NOT_FOUND);
        }

        $post_ids = $this->db->select('posts', 'id', [
            'message[~]' => '%' . $vars['id'] . '%'
        ]);

        if (!$post_ids) {
            $post_ids = [];
        }

        $original = $this->static_url . '/' . $vars['id'];
        $thumbnail = $this->static_url . '/thumb.' . $vars['id'];

        return new Response(new DTOFileInfo($original, $thumbnail, $post_ids));
    }
}
