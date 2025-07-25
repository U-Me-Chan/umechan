<?php

namespace PK;

use PK\Http\Request;
use PK\Http\Response;

abstract class RequestHandler
{
    public function __construct(
        private ?RequestHandler $successor = null
    ) {
    }

    final public function handle(Request $req): ?Response
    {
        $processed = $this->processing($req);

        if ($processed === null && $this->successor !== null) {
            $processed = $this->successor->handle($req);
        }

        return $processed;
    }

    abstract protected function processing(Request $req): ?Response;
}
