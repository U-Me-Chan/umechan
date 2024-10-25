<?php

namespace PK\Http;

use PK\Base\AResponseSchema;
use Throwable;

class Response
{
    private Throwable|null $error;

    public function __construct(
        private array|AResponseSchema $data = [],
        private int $code = 200,
        private array $headers = []
    ) {
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
