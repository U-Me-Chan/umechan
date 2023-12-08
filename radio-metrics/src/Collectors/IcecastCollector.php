<?php

namespace Ridouchire\RadioMetrics\Collectors;

use RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ridouchire\RadioMetrics\ICollector;

class IcecastCollector implements ICollector
{
    public function __construct(
        private string $url
    ) {
        $this->client = new Client([
            'timeout' => 3.0
        ]);
    }

    public function getData(): array
    {
        /** @var Response */
        $response = $this->client->request('GET', $this->url);

        if ($response->getStatusCode() == 200) {
            $content_type = $response->getHeaderLine('Content-type');

            if (preg_match('/application\/json/', $content_type) !== 1) {
                throw new \RuntimeException("Полученный ответ от источника данных в неизвестном формате");
            }

            $payload = json_decode((string) $response->getBody(), true, JSON_UNESCAPED_UNICODE);

            if (!isset($payload['icestats'])) {
                throw new RuntimeException("Нет данных о сервере стриминга");
            }

            if (!isset($payload['icestats']['source'])) {
                throw new RuntimeException("Нет данных о стриме");
            }

            if (is_array($payload['icestats']['source'])) {
                if (isset($payload['icestats']['source'][1]['stream_start'])) {
                    return [
                        'title'     => $payload['icestats']['source'][1]['server_name'],
                        'listeners' => $payload['icestats']['source'][1]['listeners']
                    ];
                }

                if (!isset($payload['icestats']['source'][0]['title'])) {
                    throw new RuntimeException("Нет данных о воспроизводимом треке");
                }

                if (!isset($payload['icestats']['source'][0]['listeners'])) {
                    throw new RuntimeException("Нет данных о слушателях");
                }

                return [
                    'title'     => $payload['icestats']['source'][0]['title'],
                    'listeners' =>  $payload['icestats']['source'][0]['listeners']
                ];
            }


            if (!isset($payload['icestats']['source']['title'])) {
                throw new RuntimeException("Нет данных о воспроизводимом треке");
            }

            if (!isset($payload['icestats']['source']['listeners'])) {
                throw new RuntimeException("Нет данных о слушателях");
            }

            return [
                'title'     => $payload['icestats']['source']['title'],
                'listeners' => $payload['icestats']['source']['listeners']
            ];
        }

        throw new RuntimeException("Источник данных не отвечает");
    }
}
