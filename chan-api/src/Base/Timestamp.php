<?php

namespace PK\Base;

class Timestamp implements \JsonSerializable
{
    public static function draft(): self
    {
        return new self(time());
    }

    public static function fromInt(int $unixtime): self
    {
        return new self($unixtime);
    }

    public static function fromString(string $datetime): self
    {
        return new self(strtotime($datetime));
    }

    public function jsonSerialize(): string
    {
        return date('d-m-Y H:i:s', $this->timestamp);
    }

    public function toInt(): int
    {
        return $this->timestamp;
    }

    public function toString(string $format = 'd-m-Y'): string
    {
        return date($format, $this->timestamp);
    }

    /**
     * Увеличивает метку времени на указанный интервал
     *
     * @param int $months  Месяцы
     * @param int $days    Дни
     * @param int $hours   Часы
     * @param int $minutes Минуты
     * @param int $seconds Секунды
     *
     * @return void
     */
    public function increase(
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0
    ): void {
        $timestamp = strtotime("+{$months}month +{$days}day +{$hours}hour +{$minutes}minute +{$seconds}second", $this->timestamp);

        if ($timestamp == false) {
            throw new \InvalidArgumentException();
        }

        $this->timestamp = $timestamp;
    }

    private function __construct(
        private int $timestamp
    ) {
    }
}
