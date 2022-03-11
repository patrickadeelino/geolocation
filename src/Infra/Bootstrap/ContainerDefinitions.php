<?php

use Application\OutputPayloadAssembler;
use Application\RawIpDataConsumer;
use Application\IpGeolocationDataProducer;
use GuzzleHttp\Client;
use Infra\Services\Cache\CacheAdapter;
use Infra\Services\Cache\RedisCacheAdapter;
use Infra\Services\IpGeolocation\IpGeolocationProvider;
use Infra\Services\IpGeolocation\IPStackProvider;
use Psr\Container\ContainerInterface;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

return [
    IpGeolocationDataProducer::class => DI\Factory(function (ContainerInterface $container) {
        $conf = new RdKafka\Conf();
        $conf->set('log_level', (string) LOG_DEBUG);
        $conf->set('debug', 'all');
        $kafkaCluster = getenv('KAFKA_CLUSTER') ?: '0.0.0.0:9092';

        $kafkaProducer = new Producer($conf);
        $kafkaProducer->addBrokers($kafkaCluster);

        return new IpGeolocationDataProducer(
            $container->get(IpGeolocationProvider::class),
            $kafkaProducer,
            $container->get(CacheAdapter::class),
            new OutputPayloadAssembler()
        );
    }),
    IpGeolocationProvider::class => DI\Factory(function (ContainerInterface $container) {
        $httpClient = new Client();

        return new IPStackProvider($httpClient);
    }),
    CacheAdapter::class => DI\Factory(function (ContainerInterface $container) {
        $redis = new Redis();
        $redis->connect('redis');

        return new RedisCacheAdapter($redis);
    }),
    RawIpDataConsumer::class => DI\Factory(function (ContainerInterface $container) {
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
