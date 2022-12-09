<?php

declare(strict_types=1);

/**
 * Spinnin' Platform - All rights reserved.
 */

namespace Martin1982\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Martin1982\OAuth2\Client\Provider\Exception\SoundCloudIdentityProviderException;
use Martin1982\OAuth2\Client\Provider\SoundCloud;
use Martin1982\OAuth2\Client\Provider\SoundCloudResourceOwner;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class FooSoundCloudProvider extends SoundCloud
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode(file_get_contents(__DIR__ . '/../fixtures/user.json'), true);
    }
}

/**
 * Class SoundCloudTest.
 *
 * @internal
 *
 * @coversNothing
 */
class SoundCloudTest extends TestCase
{
    protected SoundCloud $provider;

    protected function setUp(): void
    {
        $this->provider = new SoundCloud([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'responseType' => SoundCloud::RESPONSE_TYPE,
        ]);
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertSame('/connect', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertSame('/oauth2/token', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl(): void
    {
        $accessToken = $this->createMock(AccessToken::class);

        $url = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $uri = parse_url($url);

        $this->assertSame('/me', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getBody')->willReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $response->method('getHeader')->willReturn(['content-type' => 'json']);
        $response->method('getStatusCode')->willReturn(200);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('send')->willReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertSame('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testGetResourceOwner(): void
    {
        $provider = new FooSoundCloudProvider();

        $token = $this->createMock(AccessToken::class);

        /** @var SoundCloudResourceOwner $resourceOwner */
        $resourceOwner = $provider->getResourceOwner($token);

        $this->assertSame('0', $resourceOwner->getId());
        $this->assertSame('https://image.img/avatar.png', $resourceOwner->getAvatarUrl());
        $this->assertSame('NL', $resourceOwner->getCountry());
        $this->assertSame('john', $resourceOwner->getFirstname());
        $this->assertSame('doe', $resourceOwner->getLastname());
        $this->assertSame('john doe', $resourceOwner->getFullName());
        $this->assertSame('BE_nl', $resourceOwner->getLocale());
        $this->assertTrue($resourceOwner->getOnline());
        $this->assertSame('https://some.uri', $resourceOwner->getUri());
    }

    public function testCheckResponseFailureWithRegularError(): void
    {
        $this->expectException(SoundCloudIdentityProviderException::class);
        $this->expectExceptionMessage('No data returned.');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $data = [
            'error' => [
                'type' => 'DataException',
                'message' => 'No data returned.',
            ],
        ];

        $this->callMethod('checkResponse', [$response, $data]);
    }

    public function testCheckResponseFailureWithWrongCode(): void
    {
        $this->expectException(SoundCloudIdentityProviderException::class);
        $this->expectExceptionMessage('Wrong code.');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $data = [
            'wrong_code' => '',
        ];

        $this->callMethod('checkResponse', [$response, $data]);
    }

    /**
     * @return mixed|null
     */
    protected function callMethod($name, array $args = [])
    {
        try {
            $reflection = new \ReflectionMethod(\get_class($this->provider), $name);
            $reflection->setAccessible(true);

            return $reflection->invokeArgs($this->provider, $args);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
