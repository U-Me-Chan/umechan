<?php

namespace PK\Http\Responses;

use Throwable;
use PK\Http\Response;

class JsonResponse implements Response
{
    private ?Throwable $error = null;
    private bool $is_preformatted_json = false;
    private string $json;

    /**
     * @phpstan-ignore missingType.iterableValue,missingType.iterableValue
     */
    public function __construct(
        private array $data = [],
        private int $code = 200,
        private array $headers = [
            'Content-type: application/json',
            'Access-Control-Allow-Origin: *',
            'Acesss-Control-Allow-Methods: *',
            'Access-Control-Allow-Headers: *'
        ]
    ) {
    }

    public function setHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function setPreformattedJson(string $json): void
    {
        $this->json                 = $json;
        $this->is_preformatted_json = true;
    }

    /**
     * @return string[]
     */
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

    /**
     * @return array{
     *     type: class-string<Throwable>,
     *     message: string,
     *     file: string,
     *     line: int,
     *     trace: list<string>
     * }
     */
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
