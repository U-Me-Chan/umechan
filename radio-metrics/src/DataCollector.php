<?php

namespace Ridouchire\RadioMetrics;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class DataCollector
{
    public function __construct(
        private string $url = 'http://chan.kugi.club:3005/api/status'
    ) {
        $this->client = new Client([
            'timeout'  => 3.0
        ]);
    }

    public function getData(): array
    {
        /** @var Response */
        $response = $this->client->request('GET', $this->url);

        if ($response->getStatusCode() == 200) {
            $payload = json_decode((string) $response->getBody(), true, JSON_UNESCAPED_UNICODE);

            $result['artist'] = $payload['fileData']['id3Artist'];
            $result['track']  = $payload['fileData']['id3Title'];
            $result['playlist'] = $payload['playlistData']['name'];
            $result['listeners'] = $payload['icecastData']['icestats']['source']['listeners'];

            return $result;
        }

        return ['error' => 'no data'];
    }
}
