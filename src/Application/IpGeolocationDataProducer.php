<?php

namespace Application;

use Application\ValueObjects\RawIpData;
use Infra\Services\IpGeolocation\IpGeolocationOutput;
use Infra\Services\IpGeolocation\IpGeolocationProvider;

class IpGeolocationDataProducer implements IpGeolocationProvider
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
