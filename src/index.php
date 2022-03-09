<?php

require_once "vendor/autoload.php";

use Application\RawIpDataConsumer;

$container = new DI\ContainerBuilder();
$container->addDefinitions(require_once __DIR__ . '/Infra/Bootstrap/ContainerDefinitions.php');
$container = $container->build();

$consumer = $container->get(RawIpDataConsumer::class);
$consumer->run();
