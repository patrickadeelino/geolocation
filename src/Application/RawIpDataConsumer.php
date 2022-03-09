<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Infra\Services\IpGeolocation\IpGeolocationProvider;
use RdKafka\KafkaConsumer;

class RawIpDataConsumer
{
    public function __construct(
        private readonly KafkaConsumer $kafkaConsumer,
        private readonly IpGeolocationProvider $geolocationProducer
    ) {
    }

    public function run() : void
    {
        while (true) {
            $message = $this->kafkaConsumer->consume(120 * 1000);
            match ($message->err) {
                RD_KAFKA_RESP_ERR_NO_ERROR       => $this->sendToGeolocationProducer($message->payload),
                RD_KAFKA_RESP_ERR__TIMED_OUT     => fwrite(STDOUT, "Timed out\n"),
                RD_KAFKA_RESP_ERR__PARTITION_EOF => fwrite(STDOUT, "No more messages; will wait for more\n"),
            };
        }
    }

    private function sendToGeolocationProducer(string $payload) : void
    {
        try {
            $rawIpData = new RawIpData($payload);

            $this->geolocationProducer->getIpGeolocation($rawIpData);

            // recebe producer
            // manda
            // recebe output
            // envia para o topico de output
        } catch (\Throwable $exception) {
            fwrite(STDOUT, $exception->getMessage() . " - " . $exception->getTraceAsString() . "\n");
        }
    }
}
