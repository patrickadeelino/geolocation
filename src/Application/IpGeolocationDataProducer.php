<?php

namespace Application;

use Application\ValueObjects\RawIpData;

class IpGeolocationDataProducer
{
    public function produceIpGeolocation(RawIpData $rawIpData): void
    {
        var_dump('start produce');
        // check if clientId + ip are cached
        // if true, returns cache
        // otherwise send to geolocation provider
        // if is valid, set to cache and return
        #$this->geolocationProvider->getIpGeolocation($rawIpData);
    }
}
