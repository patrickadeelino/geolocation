<?php

require_once "vendor/autoload.php";

use Application\Consumers\RawIpConsumer;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

$container = new DI\ContainerBuilder();
$container->addDefinitions(require_once __DIR__ . '/Infra/Bootstrap/ContainerDefinitions.php');
$container = $container->build();

$consumer = $container->get(RawIpConsumer::class);
$consumer->run();
