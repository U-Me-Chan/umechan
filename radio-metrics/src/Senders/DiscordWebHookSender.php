<?php

namespace Ridouchire\RadioMetrics\Senders;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ridouchire\RadioMetrics\ISender;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use RuntimeException;

class DiscordWebHookSender implements ISender
{
    public function __construct(
        private string $discord_webhook_url
    ) {
        $this->client = new Client([
            'timeout' => 3.0,
        ]);
    }

    /**
     * Отправляет данные о воспроизводимом треке в дискорд-чат
     *
     * @param Track  $track      Воспроизводимый трек
     * @param int    $listeners  Текущее количество слушателей
     * @param string $additional Дополнительная информация
     *
     * @throws RuntimeException Если данные не были отправлены
     *
     * @return void
     */
    public function send(Track $track, int $listeners, string $additional = ''): void
    {
        /** @var Response */
        $response = $this->client->post(
            $this->discord_webhook_url,
            [
                'json' => [
                    'content' => "{$track->getName()} {$additional}"
                ]
            ]
        );

        if ($response->getStatusCode() !== 204) {
            throw new \RuntimeException("Произошла ошибка при отправке данных о воспроизводимом треке в Discord-канал");
        }
    }

    public function getName(): string
    {
        return 'DiscordWebHookSender';
    }
}
