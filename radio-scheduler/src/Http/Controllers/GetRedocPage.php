<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use React\Http\Message\Response;

final class GetRedocPage
{
    public function __invoke(): Response
    {
        $html = <<<EOD
<html>
<redoc spec-url="https://scheoble.xyz/radio/docs/openapi.json"></redoc>
<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
</html>
EOD;

        return new Response(
            Response::STATUS_OK,
            [
                'Content-type' => 'text/html'
            ],
            $html
        );
    }
}
