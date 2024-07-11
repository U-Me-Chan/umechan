<?php

namespace PK\Passports\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Passports\IPassportRepository;

final class GetPassportList
{
    public function __construct(
        private IPassportRepository $passport_repo
    ) {
    }

    public function __invoke(Request $req): Response
    {
        list($passports, $count) = $this->passport_repo->findMany();

        return new Response([
            'passports' => $passports,
            'count'     => $count
        ], 200);
    }
}
