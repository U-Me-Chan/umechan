<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class Weekday implements IRotation
{
    private const DAY     = 'day';
    private const NIGHT   = 'night';
    private const MORNING = 'morning';
    private const EVENING = 'evening';

    public function __construct(
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(): void
    {
        $time = date('H:i:s', time() + (60 * 60 * 4));

        switch ($time) {
            case '00:00:00':
            case '01:00:00':
            case '02:00:00':
            case '03:00:00':
            case '04:00:00':
            case '05:00:00':
                $this->addPlaylist(self::NIGHT);

                break;
            case '06:00:00':
            case '07:00:00':
            case '08:00:00':
                $this->addPlaylist(self::MORNING);

                break;
            case '09:00:00':
            case '10:00:00':
            case '11:00:00':
            case '12:00:00':
            case '13:00:00':
            case '14:00:00':
            case '15:00:00':
            case '16:00:00':
            case '17:00:00':
            case '18:00:00':
                $this->addPlaylist(self::DAY);

                break;
            case '19:00:00':
            case '20:00:00':
            case '21:00:00':
            case '22:00:00':
            case '23:00:00':
                $this->addPlaylist(self::EVENING);

                break;

            default:
                $this->log->debug('WeekdayStrategy: Время ещё не пришло');
                break;
        }

    }

    public function addPlaylist(string $day_part): void
    {
        if (!isset($this->getSchema()[$day_part])) {
            throw new \RuntimeException("Нет такой части суток");
        }

        $key = array_rand($this->getSchema()[$day_part], 1);

        $pls = $this->getSchema()[$day_part][$key];

        if (!$this->mpd->cropQueue()) {
            throw new \RuntimeException("WeekdayStrategy: произошла ошибка при очистке очереди");
        }

        if (!$this->mpd->addToQueue($pls)) {
            throw new \RuntimeException("WeekdayStrategy: произошла ошибка при добавлении директории в очередь");
        }

        $this->log->info("WeekdayStrategy: Ставлю {$pls}");
    }

    private function getSchema(): array
    {
        return [
            'day' => [
                'Alternative High',
                'Alternative Rock',
                'Breakcore and Lolicore',
                'Pop',
                'Pop Dance',
                'Pop Retro',
                'Korean Pop',
                'Japan Pop',
                'Ru Angst',
                'Pop Ru'
            ],
            'evening' => [
                'Alternative',
                'CityPop',
                'Digital Resistance',
                'Pop Chill Electronica',
                'Pop Retro',
                'Retrowave',
                'Slowave',
                'Vaporwave',
                'Video Game Music',
                'DnB Atmosphere',
                'DnB Liquid',
                'Pop Dance Evening'
            ],
            'morning' => [
                'Alternative',
                'Chill Hop',
                'Instrumental',
                'Jazz',
                'Pop Chill Electronica'
            ],
            'night' => [
                'Chill Electronica',
                'Chill Hop',
                'DnB Atmosphere',
                'DnB Liquid',
                'House'
            ]
        ];
    }
}
