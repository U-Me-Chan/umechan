<?php

namespace Ridouchire\RadioMetrics\Senders;

use Ridouchire\RadioMetrics\ISender;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use RuntimeException;

class ChanSender implements ISender
{
    public function __construct(
        private string $api_chan_url,
        private int $thread_id,
        private string $poster_key
    ) {
        $this->client = new Client([
            'timeout' => 3.0,
            'base_uri' => $api_chan_url,
            'verify' => true,
            'allow_redirect' => [
                'max' => 2,
                'protocol' => ['http', 'https'],
                'strict' => true
            ]
        ]);
    }

    /**
     * Отправляет данные о вопроизводимом треке на чан в тред
     *
     * @param Track $track       Воспроизводимый трек
     * @param int   $listeners   Текущее количество слушателей
     * @param string $additional Дополнительная информация
     *
     * @throws RuntimeException Если при отправке данных произошла ошибка
     *
     * @return void
     */
    public function send(Track $track, int $listeners, string $additional = ''): void
    {
        /** @var Response */
        $response = $this->client->put(
            "/api/v2/post/{$this->thread_id}",
            [
                'json' => [
                    'message' => $this->formatMessage($track, $listeners, $additional),
                    'poster'  => $this->poster_key
                ]
            ]
        );

        if ($response->getStatusCode() !== 201) {
            var_dump((string) $response->getBody());
            throw new \RuntimeException("При отправке данных о вопроизводимом треке на чан произошла ошибка");
        }
    }

    /**
     * Форматирует сообщение будущего поста
     *
     * @param Track  $track      Воспроизводимый трек
     * @param int    $listeners  Количество слушателей
     * @param string $additional Дополнительная информация
     *
     * @return string
     */
    private function formatMessage(Track $track, int $listeners, string $additional = ''): string
    {
        $text = "Сейчас играет **{$track->getName()}**";

        switch ($listeners) {
            case 1:
                $text .= ", слушает 1 слушатель";
                break;
            case 2:
            case 3:
            case 4:
                $text .= ", слушает {$listeners} слушателя";

                break;
            default:
                $text .= ", слушает {$listeners} слушателей";

                break;
        }

        $text .= $additional;

        return $text;
    }

    public function getName(): string
    {
        return 'ChanSender';
    }
}
