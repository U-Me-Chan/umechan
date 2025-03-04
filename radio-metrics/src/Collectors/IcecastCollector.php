<?php

namespace Ridouchire\RadioMetrics\Collectors;

use RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ridouchire\RadioMetrics\ICollector;

class IcecastCollector implements ICollector
{
    public function __construct(
        private Client $client
    ) {
    }

    public function getData(): array
    {
        /** @var Response */
        $response = $this->client->request('GET');

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("Источник данных не отвечает");
        }

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

        $source = $payload['icestats']['source'];

        if (isset($source['listeners'])) {
            return ['listeners' => $source['listeners']];
        }

        foreach ($source as $_source) {
            if (isset($_source['user_agent']) && $_source['user_agent'] == 'MPD') {
                return ['listeners' => $_source['listeners']];
            }
        }

        throw new \RuntimeException('Ошибка извлечения данных о слушателях');
    }
}
