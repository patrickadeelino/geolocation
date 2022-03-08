<?php

require_once "vendor/autoload.php";

use Application\Consumers\RawIpConsumer;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

$config = new Conf();
$config->set('group.id', 'geolocation-group');
$config->set('metadata.broker.list', 'kafka:9092');
$config->set('auto.offset.reset', 'earliest');

$kafkaConsumer = new KafkaConsumer($config);
$kafkaConsumer->subscribe(['test-input']);

$consumer = new RawIpConsumer($kafkaConsumer);

$consumer->run();
