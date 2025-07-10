<?php

namespace PK\Http;

interface Response
{
    public function getBody(): string;
    public function getCode(): int;
    public function getHeaders(): array;
}
