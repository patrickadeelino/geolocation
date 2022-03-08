<?php

namespace Application\Consumers;

use Application\Consumers\ValueObjects\RawIpData;
use RdKafka\KafkaConsumer;

class RawIpConsumer
{
    public function __construct(private readonly KafkaConsumer $kafkaConsumer) {}

    public function run()
    {
        while (true) {
            $message = $this->kafkaConsumer->consume(120 * 1000);
            match ($message->err) {
                RD_KAFKA_RESP_ERR_NO_ERROR => $this->sendToGeolocationProducer($message->payload),
                RD_KAFKA_RESP_ERR__PARTITION_EOF => fwrite(STDOUT, "No more messages; will wait for more\n"),
                RD_KAFKA_RESP_ERR__TIMED_OUT => fwrite(STDOUT, "Timed out\n"),
            };
        }
    }

    private function sendToGeolocationProducer(string $payload) : void
    {
        $rawIpData = new RawIpData($payload);
    }
}
