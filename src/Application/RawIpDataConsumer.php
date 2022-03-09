<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use RdKafka\KafkaConsumer;

class RawIpDataConsumer
{
    public function __construct(
        private readonly KafkaConsumer $kafkaConsumer,
        private readonly IpGeolocationDataProducer $ipGeolocationProducer
    ) {
    }

    /**
     * @param bool $mustBlockProcess Set false to consume only one message and stop execution.
     * @throws \RdKafka\Exception
     */
    public function consume(bool $mustBlockProcess = true) : void
    {
        do {
            $message = $this->kafkaConsumer->consume(120 * 1000);
            match ($message->err) {
                RD_KAFKA_RESP_ERR_NO_ERROR       => $this->produceIpGeolocation($message->payload),
                RD_KAFKA_RESP_ERR__TIMED_OUT     => fwrite(STDOUT, "Timed out\n"),
                RD_KAFKA_RESP_ERR__PARTITION_EOF => fwrite(STDOUT, "No more messages; will wait for more\n"),
            };
        } while ($mustBlockProcess);
    }

    private function produceIpGeolocation(string $payload) : void
    {
        try {
            $rawIpData = new RawIpData($payload);

            $this->ipGeolocationProducer->produceIpGeolocation($rawIpData);
        } catch (\Throwable $exception) {
            fwrite(STDOUT, $exception->getMessage() . " - " . $exception->getTraceAsString() . "\n");
        }
    }
}
