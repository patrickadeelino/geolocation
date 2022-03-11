<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Infra\Services\Cache\RedisCacheAdapter;
use Infra\Services\IpGeolocation\IpGeolocationOutput;
use Infra\Services\IpGeolocation\IpGeolocationProvider;
use Infra\Services\IpGeolocation\IPStackProvider;
use PHPUnit\Framework\TestCase;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

class IpGeolocationDataProducerTest extends TestCase
{
    public function testShouldNotSendDataWhenClientAndIpRequestExistsOnCache()
    {
        $producerSpy = $this->createMock(Producer::class);
        $producerSpy->expects($this->never())->method('newTopic');

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
        $geolocationProvider = $this->createMock(IPStackProvider::class);
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
        return new RawIpData('{"ip": "192.168.105.10", "clientId": 1, "timestamp": 1646919533}');
    }

    private function getGeolocationOutput(): IpGeolocationOutput
    {
        return new IpGeolocationOutput(0.0, 0.0, 'Brazil', 'Parana', 'Curitiba');
    }

    private function buildProducerMock(
        RawIpData $rawIpInput,
        IpGeolocationOutput $geolocationOutput
    ): Producer {
        $outputPayloadAssembler = new OutputPayloadAssembler();
        $expectedOutputPayload  = $outputPayloadAssembler->assemblyAsJsonEncoded($rawIpInput, $geolocationOutput);

        $kafkaTopic = $this->createMock(ProducerTopic::class);
        $kafkaTopic->expects($this->once())->method('produce')
                                           ->with(RD_KAFKA_PARTITION_UA, 0, $expectedOutputPayload);

        $kafkaProducer = $this->createMock(Producer::class);
        $kafkaProducer->method('newTopic')->willReturn($kafkaTopic);

        return $kafkaProducer;
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
