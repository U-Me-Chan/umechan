<?php

namespace PK\Http;

class Request
{
    private string $method;
    private string $path;
    private array $params;
    private array $headers;

    public function __construct(array $server = [], array $post = [])
    {
        $this->method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
        $this->path   = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
        $parameters   = !empty($this->path) ? parse_url($this->path) : '';

        if (isset($parameters['query'])) {
            parse_str($parameters['query'], $query);
        } else {
            $query = [];
        }

        $server['CONTENT_TYPE'] = isset($server['CONTENT_TYPE']) ? $server['CONTENT_TYPE'] : '';

        if (preg_match('/^application\/json.*/', $server['CONTENT_TYPE'])) {
            $postData = file_get_contents('php://input');
            $post = json_decode($postData, true) ?? array_merge($post, []);
        } else {
            $post = [];
        }

        $this->headers = [];

        foreach ($server as $name => $value) {
            if (preg_match('/HTTP_\w+/', $name)) {
                $this->headers[$name] = $value;
            }
        }

        $this->params = !empty($query) ? array_merge($query, $post) : $post;
        $this->path   = isset($parameters['path']) ? $parameters['path'] : '';
        $this->path   = preg_replace('/\/$/', '', $this->path);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParams(?string $key = null, mixed $defaul_value = null): mixed
    {
        if ($key) {
            if (isset($this->params[$key])) {
                return $this->params[$key];
            }

            return $defaul_value;
        }

        return $this->params;
    }

    public function getHeaders(?string $key = null): mixed
    {
        if ($key) {
            if (isset($this->headers[$key])) {
                return $this->headers[$key];
            }

            return null;
        }

        return $this->headers;
    }
}
