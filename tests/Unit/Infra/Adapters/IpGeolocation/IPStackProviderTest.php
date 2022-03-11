<?php

namespace Infra\Adapters\IpGeolocation;

use Application\ValueObjects\RawIpData;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class IPStackProviderTest extends TestCase
{
    public function testShouldThrowExceptionWithErrorMessageWhenRequestErrorOccurs()
    {
        $ipStackErrorResponse = $this->ipStackErrorResponseMock();
        $errorMessage = $ipStackErrorResponse['error']['info'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/$errorMessage/");

        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->method('sendRequest')->willReturn(new Response(
            200,
            [],
            json_encode($ipStackErrorResponse)
        ));
        $ipStackProvider = new IPStackProvider($httpClientMock);

        $ipStackProvider->getIpGeolocation(
            new RawIpData('{"ip": "192.158.1.38", "clientId": 1, "timestamp": 12390238983}')
        );
    }

    public function testShouldGetGeolocationDataFromIPStackAPI()
    {
        $ipStackSuccessResponse = $this->ipStackSuccessResponseMock();

        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->method('sendRequest')->willReturn(new Response(
            200,
            [],
            json_encode($ipStackSuccessResponse)
        ));

        $ipStackProvider = new IPStackProvider($httpClientMock);
        $geolocationOutput = $ipStackProvider->getIpGeolocation(
            new RawIpData('{"ip": "192.158.1.38", "clientId": 1, "timestamp": 12390238983}')
        );

        $this->assertEquals($ipStackSuccessResponse['city'], $geolocationOutput->city);
        $this->assertEquals($ipStackSuccessResponse['latitude'], $geolocationOutput->latitude);
        $this->assertEquals($ipStackSuccessResponse['longitude'], $geolocationOutput->longitude);
        $this->assertEquals($ipStackSuccessResponse['region_name'], $geolocationOutput->region);
        $this->assertEquals($ipStackSuccessResponse['country_name'], $geolocationOutput->country);
    }

    private function ipStackSuccessResponseMock(): array
    {
        return json_decode(
            '{
                "ip": "2001:1284:f013:2c2c:d979:cdb1:98a7:95d2",
                "country_name": "Brazil",
                "region_name": "Parana",
                "city": "Curitiba",
                "latitude": -25.427776336669922,
                "longitude": -49.27305221557617
            }',
            true
        );
    }

    private function ipStackErrorResponseMock(): array
    {
        return json_decode(
            '{
                "success": false,
                "error": {
                    "code": 105,
                    "type": "https_access_restricted",
                    "info":"The IP Address supplied is invalid."
                }
            }',
            true
        );
    }
}
