<?php

namespace Infra\Adapters\IpGeolocation;

/**
 * This class is an DTO responsible for representing the output of geolocation providers
 */
class IpGeolocationOutput
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $country,
        public readonly string $region,
        public readonly string $city,
    ) {}
}
