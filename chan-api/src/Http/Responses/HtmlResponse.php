<?php

namespace PK\Http\Responses;

use PK\Http\Response;

class HtmlResponse implements Response
{
    public function __construct(
        private string $content,
        private int $code = 200,
        private array $headers = [
            'Content-type: text/html'
        ]
    ) {
    }

    public function setHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getBody(): string
    {
        return $this->content;
    }
}
