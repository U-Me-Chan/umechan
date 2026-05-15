<?php

namespace PK\RequestHandlers;

use PK\Cache;
use PK\Http\Request;
use PK\Http\Response;
use PK\RequestHandler;

class MemcachedRequestHandler extends RequestHandler
{
    private const CACHE_KEY = 'chan-api-response-caches';
    private const EXPIRATION_TIME_SECONDS = 60 * 60 * 24;

    /** @phpstan-ignore missingType.iterableValue */
    public function __construct(
        private ?RequestHandler $sucessor,
        private Cache $cache,
        private array $cache_map = []
    ) {
        parent::__construct($sucessor);

        $_cache = $this->cache->get(self::CACHE_KEY);

        $this->cache_map = $_cache === null ? [] : $_cache;
    }

    protected function processing(Request $req): ?Response
    {
        if (in_array($req->getMethod(), ['POST', 'UPDATE', 'DELETE', 'PATCH', 'PUT'])) {
            $this->cache_map = [];
            $this->cache->delete(self::CACHE_KEY);

            return null;
        }

        $cached_result = $this->cache_map[$req->getHash()] ?? null;

        if (!$cached_result) {
            $res = $this->sucessor->handle($req);

            if ($res->getCode() !== 200) {
                return $res;
            }

            $this->cache_map[$req->getHash()] = $res;
            $this->cache->set(self::CACHE_KEY, $this->cache_map, self::EXPIRATION_TIME_SECONDS);

            return $res;
        }

        if ($cached_result instanceof Response) {
            $cached_result->setHeader('X-Pissykaka-Cache-Response: yes');

            return $cached_result;
        }

        return null;
    }
}
