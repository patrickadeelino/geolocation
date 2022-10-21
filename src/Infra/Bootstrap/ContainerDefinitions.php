<?php

use Application\OutputPayloadAssembler;
use Application\RawIpDataConsumer;
use Application\IpGeolocationDataProducer;
use Domain\Producer\MessageProducer;
use GuzzleHttp\Client;
use Infra\Adapters\Cache\RedisCacheAdapter;
use Domain\Cache\CacheAdapter;
use Domain\IpGeolocation\IpGeolocationProvider;
use Infra\Adapters\Container\GenericContainerInterface;
use Infra\Adapters\IpGeolocation\IPStackProviderAdapter;
use Infra\Adapters\Producer\KafkaProducerAdapter;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

return [
    IpGeolocationDataProducer::class => DI\Factory(function (GenericContainerInterface $container) {
        return new IpGeolocationDataProducer(
            $container->get(IpGeolocationProvider::class),
            $container->get(MessageProducer::class),
            $container->get(CacheAdapter::class),
            new OutputPayloadAssembler()
        );
    }),
    IpGeolocationProvider::class => DI\Factory(function () {
        $httpClient = new Client();

        return new IPStackProviderAdapter($httpClient);
    }),
    MessageProducer::class => DI\Factory(function () {
        $conf = new RdKafka\Conf();
        $conf->set('log_level', (string)LOG_DEBUG);
        $conf->set('debug', 'all');

        $kafkaCluster = getenv('KAFKA_CLUSTER') ?: '0.0.0.0:9092';

        $kafkaProducer = new Producer($conf);
        $kafkaProducer->addBrokers($kafkaCluster);

        return new KafkaProducerAdapter($kafkaProducer);
    }),
    CacheAdapter::class => DI\Factory(function () {
        $redis = new Redis();
        $redis->connect('redis');

        return new RedisCacheAdapter($redis);
    }),
    RawIpDataConsumer::class => DI\Factory(function (GenericContainerInterface $container) {
        $config = new Conf();
        $config->set('group.id', 'geolocation-group');
        $config->set('metadata.broker.list', 'kafka:9092');
        $config->set('auto.offset.reset', 'earliest');

        $inputTopic = getenv('INPUT_TOPIC') !== false ? getenv('INPUT_TOPIC') : 'input-topic';

        $kafkaConsumer = new KafkaConsumer($config);
        $kafkaConsumer->subscribe([$inputTopic]);

        return new RawIpDataConsumer($kafkaConsumer, $container->get(IpGeolocationDataProducer::class));
    }),
];
