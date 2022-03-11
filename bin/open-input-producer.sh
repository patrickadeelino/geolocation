#!/usr/bin/env bash

docker-compose exec kafka kafka-console-producer.sh --bootstrap-server 0.0.0.0:9092 --topic input-raw-ip-topic
