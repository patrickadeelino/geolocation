<?php

namespace Application\Producers;

use Application\Consumers\ValueObjects\RawIpData;
use Infra\Http\IpGeolocation\IpGeolocationOutput;
use Infra\Http\IpGeolocation\IpGeolocationProvider;

class IpGeolocationProducer implements IpGeolocationProvider
{
    public function getIpGeolocation(RawIpData $rawIpData): IpGeolocationOutput
    {
        // check if clientId + ip are cached
        // if true, returns cache
        // otherwise send to geolocation provider
        // if is valid, set to cache and return
        #$this->geolocationProvider->getIpGeolocation($rawIpData);
    }
}
