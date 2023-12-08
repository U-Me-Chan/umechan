<?php

namespace Ridouchire\RadioMetrics\Utils;

use InvalidArgumentException;

class Environment
{
    private array $map = [];

    public function __construct(array $env_vars)
    {
        if (!empty($env_vars)) {
            $this->assignValues($env_vars);
        }
    }

    /**
     * Устанавливает значение для ключа-переменной окружения
     *
     * @param string                     $key   Ключ
     * @param int|float|string|bool|null $value Значение
     *
     * @return void
     */
    public function __set(string $key, int|float|string|bool|null $value): void
    {
        $this->map[$key] = $value;
    }

    /**
     * Возвращает значение для ключа-переменной окружения
     *
     * @param string $key
     *
     * @throws InvalidArgumentException Если попытаться получить значение несуществующего ключа
     *
     * @return int|float|string|bool|null
     */
    public function __get(string $key): int|float|string|bool|null
    {
        if (array_key_exists($key, $this->map)) {
            return $this->map[$key];
        }

        return null;
    }

    private function assignValues(array $dataset, $key = null)
    {
        foreach ($dataset as $field => $value) {
            if (is_array($value)) {
                $this->assignValues($value, $field);
            }

            if ($key) {
                $name = strtolower($key . '.' . $field);
            } else {
                $name = strtolower($field);
            }

            $this->map[$name] = $value;
        }
    }

    private function __clone()
    {
    }
}
