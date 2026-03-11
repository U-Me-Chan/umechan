<?php

namespace PK\Http;

interface Response
{
    public function getBody(): string;
    public function getCode(): int;
    /**
     * @return string[]
     */
    public function getHeaders(): array;
    public function setHeader(string $header): void;
}
