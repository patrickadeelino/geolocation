<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Infra\Services\Cache\CacheAdapter;
use Infra\Services\IpGeolocation\IpGeolocationProvider;
use RdKafka\Producer;

class IpGeolocationDataProducer
{
    /**
     * Value in seconds, so 30 * 60 = 30 minutes.
     */
    public const REQUEST_CACHE_DURATION = 30 * 60;

    public function __construct(
        private readonly IpGeolocationProvider $geolocationProvider,
        private readonly Producer $kafkaProducer,
        private readonly CacheAdapter $cacheAdapter,
        private readonly OutputPayloadAssembler $payloadAssembler
    ) {
    }

    public function produceIpGeolocation(RawIpData $rawIpData): void
    {
        $cacheKey = sprintf('request_%s_%s', $rawIpData->clientId(), $rawIpData->ip());
        if ($this->cacheAdapter->get($cacheKey) !== null) {
            fwrite(
                STDOUT,
                sprintf("Request for client (%s) and ip (%s) is cached and will not be processed. \n", $rawIpData->clientId(), $rawIpData->ip())
            );
            return;
        }

        $geolocationOutput = $this->geolocationProvider->getIpGeolocation($rawIpData);
        $outputPayload     = $this->payloadAssembler->assemblyAsJsonEncoded($rawIpData, $geolocationOutput);

        $this->sendPayloadToOutputTopic($outputPayload);

        $this->cacheAdapter->set($cacheKey, 'cached', self::REQUEST_CACHE_DURATION);
    }

    private function sendPayloadToOutputTopic(string $payload)
    {
        $outputTopicName = getenv('OUTPUT_TOPIC') ?: 'default-output-topic';

        $outputTopic = $this->kafkaProducer->newTopic($outputTopicName);
        $outputTopic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);

        fwrite(STDOUT, sprintf("Payload sent to (%s) topic. \n", $outputTopicName));
    }
}
