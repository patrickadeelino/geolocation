<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Infra\Services\IpGeolocation\IpGeolocationOutput;

class OutputPayloadAssembler
{
    public function assemblyAsJsonEncoded(RawIpData $rawIpData, IpGeolocationOutput $geolocationOutput): string
    {
        return json_encode([
            'ip' => $rawIpData->ip(),
            'timestamp' => $rawIpData->timestamp(),
            'clientId' => $rawIpData->clientId(),
            'latitude' => $geolocationOutput->latitude,
            'longitude' => $geolocationOutput->longitude,
            'country' => $geolocationOutput->country,
            'region' => $geolocationOutput->region,
            'city' => $geolocationOutput->city,
        ]);
    }
}
