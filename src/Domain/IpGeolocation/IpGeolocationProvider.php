<?php

namespace Infra\Adapters\IpGeolocation;

use Application\ValueObjects\RawIpData;

interface IpGeolocationProvider
{
    public function getIpGeolocation(RawIpData $rawIpData) : IpGeolocationOutput;
}
