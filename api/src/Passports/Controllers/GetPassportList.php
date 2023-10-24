<?php

namespace PK\Passports\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use Pk\Passports\PassportStorage;

final class GetPassportList
{
    public function __construct(
        private PassportStorage $passport_repo
    ) {
    }

    public function __invoke(Request $req): Response
    {
        list($passports, $count) = $this->passport_repo->fetch();

        return new Response([
            'passports' => $passports,
            'count'     => $count
        ], 200);
    }
}
