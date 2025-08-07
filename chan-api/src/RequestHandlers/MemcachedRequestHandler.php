<?php

namespace PK\RequestHandlers;

use PK\Http\Request;
use PK\Http\Response;
use PK\RequestHandler;

class MemcachedRequestHandler extends RequestHandler
{
    private const CACHE_KEY = 'chan-api-response-caches';

    private \Memcached $memcached;

    public function __construct(
        private ?RequestHandler $sucessor,
        private string $host = 'memcached',
        private int $port = 11211,
        private array $cache_map = []
    ) {
        parent::__construct($sucessor);

        $this->memcached = new \Memcached();
        $this->memcached->addServer($this->host, $this->port);

        $_cache = $this->memcached->get(self::CACHE_KEY);

        $this->cache_map = $_cache === false ? [] : $_cache;
    }

    protected function processing(Request $req): ?Response
    {
        if ($req->getMethod() !== 'GET') {
            $this->memcached->delete(self::CACHE_KEY); // удаляем кеш всех запросов при любой операции записи

            return null;
        }

        if (!isset($this->cache_map[$req->getHash()])) {
            $res = $this->sucessor->handle($req);

            $this->cache_map[$req->getHash()] = $res;

            $this->memcached->set(self::CACHE_KEY, $this->cache_map);

            return $res;
        }

        $res = $this->cache_map[$req->getHash()];

        if ($res !== false && $res instanceof Response) {
            return $res;
        }

        return null;
    }
}
