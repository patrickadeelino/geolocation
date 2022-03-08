<?php

namespace Application\Consumers\ValueObjects;

class RawIpData
{
    private string $ipAddress;
    private int $clientId;
    private \DateTimeImmutable $date;

    public function __construct(string $encodedPayload)
    {
        $decodedPayload = $this->decodedPayload($encodedPayload);

        var_dump(json_decode($encodedPayload));die;
    }

    private function decodedPayload(string $encodedPayload) : array
    {
        $decodedPayload = json_decode($encodedPayload, true);

        if (!is_array($decodedPayload)) {
            throw new \InvalidArgumentException('Invalid payload parameter provided');
        }

        return $decodedPayload;
    }
}
