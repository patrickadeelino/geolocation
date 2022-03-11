#!/usr/bin/env bash

printf "Consuming... \n"
docker-compose exec kafka kafka-console-consumer.sh --bootstrap-server 0.0.0.0:9092 --topic output-ip-geolocation-topic
