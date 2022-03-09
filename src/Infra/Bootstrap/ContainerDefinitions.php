<?php

use Application\RawIpDataConsumer;
use Application\IpGeolocationDataProducer;
use Psr\Container\ContainerInterface;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

return [
    RawIpDataConsumer::class => DI\Factory(function (ContainerInterface $container) {
        $config = $container->get(Conf::class);

        $inputTopic = getenv('INPUT_TOPIC') !== false ? getenv('INPUT_TOPIC') : 'input-topic';

        $kafkaConsumer = new KafkaConsumer($config);
        $kafkaConsumer->subscribe([$inputTopic]);

        return new RawIpDataConsumer($kafkaConsumer, new IpGeolocationDataProducer());
    }),
    Conf::class => DI\Factory(function (ContainerInterface $container) {
        $config = new Conf();
        $config->set('group.id', 'geolocation-group');
        $config->set('metadata.broker.list', 'kafka:9092');
        $config->set('auto.offset.reset', 'earliest');

        return $config;
    })
];
