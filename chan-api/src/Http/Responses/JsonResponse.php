<?php

namespace PK\Http\Responses;

use Throwable;
use PK\Http\Response;

class JsonResponse implements Response
{
    private array $data;
    private int $code;
    private array $headers;
    private ?Throwable $error;
    private bool $is_preformatted_json = false;
    private string $json;

    public function __construct(
        array $data = [],
        int $code = 200,
        array $headers = [
            'Content-type: application/json',
            'Access-Control-Allow-Origin: *',
            'Acesss-Control-Allow-Methods: *',
            'Access-Control-Allow-Headers: *'
        ]
    ) {
        $this->data = $data;
        $this->code = $code;
        $this->headers = $headers;
        $this->error = null;
    }

    public function setPreformattedJson(string $json): void
    {
        $this->json = $json;
        $this->is_preformatted_json = true;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        if ($this->is_preformatted_json) {
            return $this->json;
        }

        $body = [
            'payload' => $this->data,
            'error'   => null
        ];

        if ($this->error) {
            $body['error'] = $this->getErrorData($this->error);
        }

        return json_encode($body);
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
