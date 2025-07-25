<?php

namespace PK\RequestHandlers;

use PK\Http\Request;
use PK\Http\Response;
use PK\RequestHandler;

class MemcachedRequestHandler extends RequestHandler
{
    private \Memcached $memcached;

    public function __construct(
        private string $host = 'memcached',
        private int $port = 11211,
        private ?RequestHandler $sucessor = null
    ) {
        parent::__construct($sucessor);

        $this->memcached = new \Memcached();
        $this->memcached->addServer($this->host, $this->port);
    }

    protected function processing(Request $req): ?Response
    {
        if ($req->getMethod() !== 'GET') {
            return null;
        }

        if ($req->getParams('no_cache') == true) {
            return null;
        }

        $res = $this->memcached->get($req->getHash());

        if ($res !== false && $res instanceof Response) {
            return $res;
        }

        if ($res === false) {
            $res = $this->sucessor->handle($req);

            $this->memcached->set($req->getHash(), $res, 30);

            return $res;
        }

        return null;
    }
}
