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
    private ?Producer $producer = null;
    private Conf $conf;

    /**
     * @param string[] $kafka_addrs
     */
    public function __construct(
        public array $kafka_addrs
    ) {
        $this->conf = new Conf();
        $this->conf->set('log_level', (string) LOG_DEBUG);
        $this->conf->set('metadata.broker.list', implode(',', $this->kafka_addrs));
        $this->conf->set('acks', 'all');
        $this->conf->set('socket.timeout.ms', (string) 10);
        // $this->conf->set('socket.blocking.max.ms', 10); // deprecated, synonym for param socket.timeout.ms
        $this->conf->set('queue.buffering.max.ms', (string) 1);
    }

    public function publish(Message $message): void
    {
        /** @var ProducerTopic */
        $topic = $this->getConnection()->newTopic($message->topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, RD_KAFKA_MSG_F_BLOCK, (string) $message);

        $this->getConnection()->poll(100);

        if ($this->getConnection()->flush(1000) !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new RuntimeException('Не смог отправить сообщение в Kafka');
        }
    }

    private function getConnection(): Producer
    {
        if (!$this->producer) {
            $this->producer = new Producer($this->conf);
        }

        return $this->producer;
    }
}
