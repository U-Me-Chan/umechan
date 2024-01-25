<?php

namespace PK\Tracks\Track;

use PK\Base\Timestamp;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Track')]
class Track implements \JsonSerializable
{
    /**
     * Создаёт черновик композиции
     *
     * @param string $artist   Исполнитель
     * @param string $title    Название
     * @param string $path     Путь до файла
     * @param int    $duration Длительность в секундах
     *
     * @return self
     */
    public static function draft(string $artist, string $title, string $path, int $duration = 0): self
    {
        return new self(0, $artist, $title, $path, Timestamp::draft(), Timestamp::draft(), $duration);
    }

    /**
     * Создаёт композицию из состояния в виде ассоциативного хеша
     *
     * @param array $state Состояние
     *
     * @return self
     */
    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['artist'],
            $state['title'],
            $state['path'],
            Timestamp::fromInt($state['first_playing']),
            Timestamp::fromInt($state['last_playing']),
            $state['duration'],
            $state['play_count'],
            $state['estimate'],
            $state['hash']
        );
    }

    /**
     * Устанавливает значение свойства
     *
     * @param string $name  Свойство
     * @param mixed  $value Значение
     *
     * @throws InvalidArgumentException Если нет такого свойства
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (!in_array($name, array_keys(get_object_vars($this)))) {
            throw new \InvalidArgumentException("Неизвестное свойство: {$name}");
        }

        if ($name == 'first_playing' || $name == 'last_playing') {
            $value = Timestamp::fromInt($value);
        }

        $this->$name = $value;
    }

    /**
     * Возвращает значение свойства
     *
     * @param string $name Свойство
     *
     * @throws InvalidArgumentException Если нет такого свойства
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (!in_array($name, array_keys(get_object_vars($this)))) {
            throw new \InvalidArgumentException("Неизвестное свойство: {$name}");
        }

	return $this->$name;
    }

    /**
     * Возвращает представление информации о композиции в виде ассоциативного хеша для последующей сериализации в JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * Возвращает представление информации о композиции в виде ассоциативного хеша для последующей передаче интерфейсу к СУБД
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'artist'        => $this->artist,
            'title'         => $this->title,
            'path'          => $this->path,
            'first_playing' => $this->first_playing->toInt(),
            'last_playing'  => $this->last_playing->toInt(),
            'duration'      => $this->duration,
            'play_count'    => $this->play_count,
            'estimate'      => $this->estimate,
            'hash'          => $this->hash
        ];
    }

    private function __construct(
        #[OA\Property(description: 'Идентификатор композиции')]
        private int $id,
        #[OA\Property(description: 'Имя исполнителя')]
        private string $artist,
        #[OA\Property(description: 'Имя композиции')]
        private string $title,
        #[OA\Property(description: 'Относительный путь до файла')]
        private string $path,
        #[OA\Property(description: 'Unixtime-метка добавления', type: 'integer')]
        private Timestamp $first_playing,
        #[OA\Property(description: 'Unixtime-метка последнего воспроизведения', type: 'integer')]
        private Timestamp $last_playing,
        #[OA\Property(description: 'Длительность в секундах')]
        private int $duration = 0,
        #[OA\Property(description: 'Количество воспроизведений')]
        private int $play_count = 0,
        #[OA\Property(description: 'Оценка')]
        private int $estimate = 0,
        #[OA\Property(description: 'Хеш-сумма')]
        private ?string $hash = null
    ) {
    }
}
