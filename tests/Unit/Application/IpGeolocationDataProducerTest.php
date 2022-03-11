<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Domain\IpGeolocation\IpGeolocationOutput;
use Domain\Producer\MessageProducer;
use Infra\Adapters\Cache\RedisCacheAdapter;
use Domain\IpGeolocation\IpGeolocationProvider;
use Infra\Adapters\IpGeolocation\IPStackProviderAdapter;
use Infra\Adapters\Producer\KafkaProducerAdapter;
use PHPUnit\Framework\TestCase;

class IpGeolocationDataProducerTest extends TestCase
{
    public function testShouldNotSendDataWhenClientAndIpRequestExistsOnCache()
    {
        $producerSpy = $this->createMock(MessageProducer::class);
        $producerSpy->expects($this->never())->method('produceMessage');

        $geolocationProviderSpy = $this->createMock(IpGeolocationProvider::class);
        $geolocationProviderSpy->expects($this->never())->method('getIpGeolocation');

        $rawIpInput = $this->getRawIpData();
        $cacheAdapter = $this->createMock(RedisCacheAdapter::class);
        $cacheAdapter->method('get')
            ->with($this->buildCacheKey($rawIpInput))
            ->willReturn('cached');

        $service = new IpGeolocationDataProducer($geolocationProviderSpy, $producerSpy, $cacheAdapter, new OutputPayloadAssembler());

        $service->produceIpGeolocation($rawIpInput);
    }

    public function testShouldSendProcessedDataToOutputTopic()
    {
        $geolocationOutput = $this->getGeolocationOutput();
        $geolocationProvider = $this->createMock(IPStackProviderAdapter::class);
        $geolocationProvider->method('getIpGeolocation')->willReturn($geolocationOutput);

        $rawIpInput = $this->getRawIpData();
        $outputPayloadAssembler = new OutputPayloadAssembler();
        $kafkaProducer = $this->buildProducerMock($rawIpInput, $geolocationOutput);

        $cacheAdapter = $this->buildCacheAdapterExpectingKeyToBeSet($rawIpInput);
        $service = new IpGeolocationDataProducer($geolocationProvider, $kafkaProducer, $cacheAdapter, $outputPayloadAssembler);
        $service->produceIpGeolocation($rawIpInput);
    }

    private function getRawIpData(): RawIpData
    {
        return new RawIpData('{"ip": "192.168.105.10", "clientId": 1}', time());
    }

    private function getGeolocationOutput(): IpGeolocationOutput
    {
        return new IpGeolocationOutput(0.0, 0.0, 'Brazil', 'Parana', 'Curitiba');
    }

    private function buildProducerMock(
        RawIpData $rawIpInput,
        IpGeolocationOutput $geolocationOutput
    ): MessageProducer {
        $outputPayloadAssembler = new OutputPayloadAssembler();
        $expectedOutputPayload  = $outputPayloadAssembler->assemblyAsJsonEncoded($rawIpInput, $geolocationOutput);

        $producer = $this->createMock(KafkaProducerAdapter::class);
        $producer->expects($this->once())->method('produceMessage')
                                           ->with($expectedOutputPayload);

        return $producer;
    }

    private function buildCacheKey(RawIpData $rawIpInput): string
    {
        return sprintf("request_%s_%s", $rawIpInput->clientId(), $rawIpInput->ip());
    }

    private function buildCacheAdapterExpectingKeyToBeSet(RawIpData $rawIpInput) : RedisCacheAdapter
    {
        $cacheAdapter = $this->createMock(RedisCacheAdapter::class);
        $cacheKey = $this->buildCacheKey($rawIpInput);
        $cacheValue = 'cached';
        $cacheAdapter->expects($this->once())
                     ->method('set')
                     ->with($cacheKey, $cacheValue, IpGeolocationDataProducer::REQUEST_CACHE_DURATION);

        return $cacheAdapter;
    }
}
