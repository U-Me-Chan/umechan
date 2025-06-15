<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Http\Request;

class RequestTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function test(
        array $server_params,
        array $post_params,
        string $method,
        string $path,
        array $params,
        array $headers
    ): void
    {
        $req = new Request($server_params, $post_params);

        $this->assertEquals($method, $req->getMethod());
        $this->assertEquals($path, $req->getPath());
        $this->assertEquals($params, $req->getParams());
        $this->assertEquals($headers, $req->getHeaders());
    }

    public static function dataProvider(): array
    {
        return [
            [
                [
                    'HTTP_COOKIE' => 'test',
                    'HTTP_CONTENT_TYPE' => 'application/json'
                ],
                [],
                'GET',
                '',
                [],
                [
                    'HTTP_COOKIE' => 'test',
                    'HTTP_CONTENT_TYPE' => 'application/json'
                ]
            ],
            [
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI'    => '/api/boards?limit=10&offset=0'
                ],
                [],
                'GET',
                '/api/boards',
                [
                    'limit' => 10,
                    'offset' => 0
                ],
                []
            ],
            [
                [
                    'REQUEST_METHOD' => 'PUT',
                    'REQUEST_URI'    => '/api/v2/post/123',
                    'CONTENT_TYPE'   => 'application/json'
                ],
                [
                    'message' => 'test'
                ],
                'PUT',
                '/api/v2/post/123',
                [
                    'message' => 'test'
                ],
                []
            ]
        ];
    }
}
