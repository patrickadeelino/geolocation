<?php

namespace Infra\Services\IpGeolocation;

use Application\ValueObjects\RawIpData;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

/**
 * This class is responsible for looking up the geolocation of an ip using the IPStack API
 *
 * @see https://ipstack.com/documentation
 */
class IPStackProvider implements IpGeolocationProvider
{
    private const IP_STACK_API_ENDPOINT = 'http://api.ipstack.com';

    public function __construct(private readonly ClientInterface $httpClient) {}

    public function getIpGeolocation(RawIpData $rawIpData): IpGeolocationOutput
    {
        $responseBody = $this->getGeolocationDataFromAPI($rawIpData);

        $requestUnsuccessful = array_key_exists('success', $responseBody) && $responseBody['success'] === false;
        if ($requestUnsuccessful) {
            throw new \RuntimeException($responseBody['error']['info']);
        }

        return new IpGeolocationOutput(
            $responseBody['latitude'],
            $responseBody['longitude'],
            $responseBody['country_name'],
            $responseBody['region_name'],
            $responseBody['city'],
        );
    }

    private function getGeolocationDataFromAPI(RawIpData $rawIpData) : array
    {
        $url = $this->mountApiEndpoint($rawIpData->ip());
        $request = new Request('GET', $url);

        $response = $this->httpClient->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function mountApiEndpoint(string $ip) : string
    {
        $apiAccessKey = getenv('IPSTACK_ACCESS_KEY') ?: '960febd96ff9ab3856cfceb235f7307f';

        return sprintf(self::IP_STACK_API_ENDPOINT . "/%s?access_key=%s", $ip, $apiAccessKey);
    }
}
