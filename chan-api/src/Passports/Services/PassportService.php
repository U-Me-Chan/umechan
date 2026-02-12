<?php

namespace PK\Passports\Services;

use PK\Events\ChanEventBuilder;
use PK\Events\MessageBroker;
use PK\Passports\Events\ChanEvents\CreatePassport;
use PK\Passports\Events\ChanEvents\CreatePassportPayload;
use PK\Passports\Exceptions\NameOrKeyIsForbiddenException;
use PK\Passports\Passport;
use PK\Passports\PassportStorage;

final class PassportService
{
    public function __construct(
        private PassportStorage $passport_storage,
        private MessageBroker $message_broker,
        private ChanEventBuilder $chan_event_builder,
        private string $default_name
    ) {
    }

    public function getList(): array
    {
        return $this->passport_storage->fetch();
    }

    public function findByHash(string $hash): Passport
    {
        return $this->passport_storage->findOne(['hash' => $hash]);
    }

    public function createPassport(string $name, string $key): void
    {
        if ($name == $this->default_name || $key == $this->default_name) {
            throw new NameOrKeyIsForbiddenException("Нельзя использовать имя автора по умолчанию для любого из параметров: {$this->default_name}");
        }

        $passport = Passport::draft($name, $key);

        $this->passport_storage->save($passport);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                CreatePassport::class,
                new CreatePassportPayload(
                    $passport->name,
                    $passport->hash
                )
            )
        );
    }
}
