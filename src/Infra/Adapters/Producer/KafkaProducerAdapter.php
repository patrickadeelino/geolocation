<?php

namespace Infra\Adapters\Producer;

use Domain\Producer\MessageProducer;
use RdKafka\Producer;

class KafkaProducerAdapter implements MessageProducer
{
    public function __construct(private readonly Producer $kafkaProducer)
    {
    }

    public function produceMessage(string $payload): void
    {
        $outputTopicName = getenv('OUTPUT_TOPIC') ?: 'default-output-topic';

        $outputTopic = $this->kafkaProducer->newTopic($outputTopicName);
        $outputTopic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);

        fwrite(STDOUT, sprintf("Payload sent to (%s) topic. \n", $outputTopicName));
    }
}
