<?php

namespace PK\Http;

class Response
{
    private $data;
    private $code;
    private $headers;
    private $error;

    public function __construct(array $data = [], int $code = 200, array $headers = [])
    {
        $this->data = $data;
        $this->code = $code;
        $this->headers = $headers;
        $this->error = null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        $body = [
            'payload' => $this->data,
            'error'   => null
        ];

        if ($this->error) {
            $body['error'] = $this->getErrorData($this->error);
        }

        return $body;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setException(\Throwable $e): self
    {
        $this->error = $e;

        return $this;
    }

    private function getErrorData(\Throwable $e): array
    {
        return [
            'type'    => get_class($e),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => explode(PHP_EOL, $e->getTraceAsString())
        ];
    }
}
