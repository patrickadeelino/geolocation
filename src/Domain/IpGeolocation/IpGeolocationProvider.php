<?php

namespace Domain\IpGeolocation;

use Application\ValueObjects\RawIpData;

interface IpGeolocationProvider
{
    public function getIpGeolocation(RawIpData $rawIpData) : IpGeolocationOutput;
}
