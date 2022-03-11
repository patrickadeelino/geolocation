<?php

declare(strict_types=1);

namespace Application\ValueObjects;

use PHPUnit\Framework\TestCase;

class RawIpDataTest extends TestCase
{
    /**
     * @dataProvider invalidPayloadProvider
     */
    public function testShouldThrowExceptionWhenPayloadIsAInvalidJson(string $invalidPayload)
    {
       $this->expectException(\InvalidArgumentException::class);

        new RawIpData($invalidPayload, 1);
    }

    public function testShouldParseDataWhenPayloadIsValid()
    {
        $ip        = "127.0.0.1";
        $clientId  = 1;
        $timestamp = time();
        $validPayload = sprintf('{"ip": "%s", "clientId": %d}', $ip, $clientId);

        $rawIpData = new RawIpData($validPayload, $timestamp);

        $this->assertEquals($rawIpData->ip(), $ip);
        $this->assertEquals($rawIpData->clientId(), $clientId);
        $this->assertEquals($rawIpData->timestamp, $timestamp);
    }

    public function invalidPayloadProvider() : array
    {
        return [
            'not json' => [
                '123',
            ],
            'empty string' => [
                '',
            ],
            'payload with invalid ip' => [
                '{"ip": "127.0.1", "clientId": 1}',
            ],
            'payload without clientId' => [
                '{"ip": "127.0.0.1"}',
            ],
        ];
    }
}
