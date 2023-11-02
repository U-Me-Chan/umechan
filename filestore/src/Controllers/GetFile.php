<?php

namespace IH\Controllers;

use Medoo\Medoo;
use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            return new Response('', 400);
        }

        $data = [];

        $post_datas = $this->db->select('posts', 'id', [
            'message[~]' => '%' . $vars['id'] . '%'
        ]);

        if (!$post_datas) {
            $data['post_ids'] = [];
        } else {
            $data['post_ids'] = $post_datas;
        }

        $data['original'] = $this->static_url . '/' . $vars['id'];
        $data['thumbnail'] = $this->static_url . '/thumb.' . $vars['id'];

        return new Response(json_encode($data), Response::HTTP_OK, [
                'Content-type'                 => 'application/json',
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => '*',
                'Access-Control-Allow-Headers' => '*'
        ]);
    }
}
