<?php

namespace PK\Events\MessageBrokers;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use PK\Events\Message;
use PK\Events\MessageBroker;
use RuntimeException;

final class KafkaWrapper implements MessageBroker
{
    private Producer $producer;

    public function __construct(
        public array $kafka_addrs
    ) {
        $conf = new Conf();
        $conf->set('log_level', (string) LOG_DEBUG);
        $conf->set('metadata.broker.list', implode(',', $this->kafka_addrs));
        $conf->set('acks', 'all');
        $conf->set('socket.timeout.ms', (string) 10);
        // $conf->set('socket.blocking.max.ms', 10); // deprecated, synonym for param socket.timeout.ms
        $conf->set('queue.buffering.max.ms', (string) 1);

        $this->producer = new Producer($conf);

        $this->isConnect();
    }

    public function publish(Message $message): void
    {
        /** @var ProducerTopic */
        $topic = $this->producer->newTopic($message->topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, RD_KAFKA_MSG_F_BLOCK, (string) $message);

        $this->producer->poll(100);

        if ($this->producer->flush(1000) !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new RuntimeException('Не смог отправить сообщение в Kafka');
        }
    }

    public function flush(): void
    {
        $this->producer->flush(1000);
    }

    private function isConnect(): void
    {
        /** @phpstan-ignore method.notFound */
        if ($this->producer->getControllerId(10) == -1) {
            throw new RuntimeException('Нет связи с брокером');
        }

        //NOTE: я не нашёл иного способа проверить подключение к брокеру
    }
}
