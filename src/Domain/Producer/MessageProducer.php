<?php

namespace Domain\Producer;

interface MessageProducer
{
    /**
     * This method produces a message to an output destination, it can be a Kafka Topic, Rabbit Queue or even a text file
     *
     * @param string $payload
     */
    public function produceMessage(string $payload) : void;
}
