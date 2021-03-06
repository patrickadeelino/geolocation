<?php

namespace Application\ValueObjects;

class RawIpData
{
    private string $ip;
    private int $clientId;

    public function __construct(string $encodedPayload, public readonly int $timestamp)
    {
        $decodedPayload = $this->decodedPayload($encodedPayload);

        $this->ip = $this->extractPayloadIp($decodedPayload);
        $this->clientId = $this->extractPayloadClientId($decodedPayload);
    }

    public function ip(): string
    {
        return $this->ip;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    private function decodedPayload(string $encodedPayload): array
    {
        $decodedPayload = json_decode($encodedPayload, true);

        if (!is_array($decodedPayload)) {
            throw new \InvalidArgumentException(sprintf('Invalid payload (%s) provided', $encodedPayload));
        }

        return $decodedPayload;
    }

    private function extractPayloadIp(array $decodedPayload): string
    {
        $ip = $decodedPayload['ip'] ?? null;

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(sprintf('Invalid ip (%s) provided', $ip));
        }

        return $ip;
    }

    private function extractPayloadClientId(array $decodedPayload): int
    {
        $clientId = $decodedPayload['clientId'] ?? null;

        if ($clientId === null) {
            throw new \InvalidArgumentException('Invalid client id provided');
        }

        return (int)$clientId;
    }
}
