<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Domain\Cache\CacheAdapter;
use Domain\IpGeolocation\IpGeolocationProvider;
use Domain\Producer\MessageProducer;

class IpGeolocationDataProducer
{
    /**
     * Value in seconds, so 30 * 60 = 30 minutes.
     */
    public const REQUEST_CACHE_DURATION = 30 * 60;

    public function __construct(
        private readonly IpGeolocationProvider $geolocationProvider,
        private readonly MessageProducer $producer,
        private readonly CacheAdapter $cacheAdapter,
        private readonly OutputPayloadAssembler $payloadAssembler
    ) {
    }

    public function produceIpGeolocation(RawIpData $rawIpData): void
    {
        $cacheKey = sprintf('request_%s_%s', $rawIpData->clientId(), $rawIpData->ip());
        if ($this->cacheAdapter->get($cacheKey) !== null) {
            $this->writeLog($rawIpData);

            return;
        }

        $geolocationOutput = $this->geolocationProvider->getIpGeolocation($rawIpData);
        $outputPayload     = $this->payloadAssembler->assemblyAsJsonEncoded($rawIpData, $geolocationOutput);

        $this->producer->produceMessage($outputPayload);

        $this->cacheAdapter->set($cacheKey, 'cached', self::REQUEST_CACHE_DURATION);
    }

    private function writeLog(RawIpData $rawIpData): void
    {
        fwrite(
            STDOUT,
            sprintf(
                "Request for client (%s) and ip (%s) is cached and will not be processed. \n",
                $rawIpData->clientId(),
                $rawIpData->ip()
            )
        );
    }
}
