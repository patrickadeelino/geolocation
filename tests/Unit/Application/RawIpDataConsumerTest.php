<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;
use RdKafka\Message;

class RawIpDataConsumerTest extends TestCase
{
    public function testShouldConsumeMessageAndSendToProducer()
    {
        $kafkaMessage = $this->getKafkaMessage();

        $kafkaConsumer = $this->createMock(KafkaConsumer::class);
        $kafkaConsumer->method('consume')->willReturn($kafkaMessage);

        $ipGeolocationProducer = $this->createMock(IpGeolocationDataProducer::class);
        $ipGeolocationProducer->expects($this->once())
                               ->method('produceIpGeolocation')
                               ->with(new RawIpData($kafkaMessage->payload));

        $consumer = new RawIpDataConsumer($kafkaConsumer, $ipGeolocationProducer);
        $consumer->consume(false);
    }

    private function getKafkaMessage() : Message
    {
        $kafkaMessage = new Message();
        $kafkaMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $kafkaMessage->payload = '{"ip": "127.0.0.1", "clientId": 1, "timestamp": 12390238983}';

        return $kafkaMessage;
    }
}
