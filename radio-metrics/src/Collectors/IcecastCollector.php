<?php

namespace Ridouchire\RadioMetrics\Collectors;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ridouchire\RadioMetrics\ICollector;
use Ridouchire\RadioMetrics\DTOs\CollectorData;
use RuntimeException;

class IcecastCollector implements ICollector
{
    public function __construct(
        private string $url
    ) {
        $this->client = new Client([
            'timeout' => 3.0
        ]);
    }

    public function getData(): CollectorData
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
                    return new CollectorData(
                        $payload['icestats']['source'][1]['server_name'],
                        $payload['icestats']['source'][1]['listeners']
                    );
                }

                if (!isset($payload['icestats']['source'][0]['title'])) {
                    throw new RuntimeException("Нет данных о воспроизводимом треке");
                }

                if (!isset($payload['icestats']['source'][0]['listeners'])) {
                    throw new RuntimeException("Нет данных о слушателях");
                }

                throw new  RuntimeException("Ни один из стримов не имеет данных о треке и слушателях");
            }


            if (!isset($payload['icestats']['source']['title'])) {
                throw new RuntimeException("Нет данных о воспроизводимом треке");
            }

            if (!isset($payload['icestats']['source']['listeners'])) {
                throw new RuntimeException("Нет данных о слушателях");
            }

            return new CollectorData($payload['icestats']['source']['title'], $payload['icestats']['source']['listeners']);
        }

        throw new RuntimeException("Источник данных не отвечает");
    }
}
