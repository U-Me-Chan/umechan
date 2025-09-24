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

        /** @phpstan-ignore-next-line */
        $post_ids = $this->db->select('posts', 'id', [
            'message[~]' => '%' . $vars['id'] . '%'
        ]);

        if (!$post_ids) { // @phpstan-ignore booleanNot.alwaysTrue
            $post_ids = [];
        }

        $original = $this->static_url . '/' . $vars['id'];
        $thumbnail = $this->static_url . '/thumb.' . $vars['id'];

        if (substr($thumbnail, -3) == 'mp4' || substr($thumbnail, -4) == 'webm') {
            $thumbnail = $thumbnail . '.' . 'jpeg';
        }

        return new Response(new DTOFileInfo($original, $thumbnail, $post_ids));
    }
}
