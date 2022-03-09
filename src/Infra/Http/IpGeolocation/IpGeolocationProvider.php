<?php

namespace Infra\Http\IpGeolocation;

use Application\Consumers\ValueObjects\RawIpData;

interface IpGeolocationProvider
{
    public function getIpGeolocation(RawIpData $rawIpData) : IpGeolocationOutput;
}
