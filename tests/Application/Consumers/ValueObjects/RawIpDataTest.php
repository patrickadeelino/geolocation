<?php

namespace Application\Consumers\ValueObjects;

use PHPUnit\Framework\TestCase;

class RawIpDataTest extends TestCase
{
    public function testShouldThrowExceptionWhenPayloadIsAInvalidJson()
    {
        $this->expectException();

        $ip = new RawIpData('123');

        var_dump($ip);
    }
}
