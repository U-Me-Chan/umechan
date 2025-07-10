<?php

namespace PK\OpenApi\Controllers;

use PK\Http\Responses\HtmlResponse;

final class GetRedocPage
{
    public function __construct(
        private string $domain
    ) {
    }

    public function __invoke(): HtmlResponse
    {
        $html = <<<EOD
<html>
<head><title>Chan API</title></head>
<redoc spec-url="//{$this->domain}/api/v2/_/openapi.json"></redoc>
<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
</html>
EOD;

        return new HtmlResponse($html, 200);
    }
}
