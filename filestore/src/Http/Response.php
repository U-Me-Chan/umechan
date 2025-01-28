<?php

namespace IH\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use IH\DTO\DTO;

final class Response extends SymfonyResponse
{
    public function __construct(
        private DTO $data,
        private int $http_code = SymfonyResponse::HTTP_OK
    ) {
        parent::__construct(
            json_encode($data->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS),
            $http_code,
            $this->getResponseHeaders()
        );
    }

    private function getResponseHeaders(): array
    {
        return [
            'Content-type'                 => 'application/json',
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => '*'
        ];
    }
}
