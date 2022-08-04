<?php

declare(strict_types=1);

namespace Martin1982\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Martin1982\OAuth2\Client\Provider\Exception\SoundCloudIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class SoundCloud extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public const BASE_SOUNDCLOUD_URL = 'https://api.soundcloud.com/';
    public const RESPONSE_TYPE = 'code';

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl(): string
    {
        return self::BASE_SOUNDCLOUD_URL . 'connect';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::BASE_SOUNDCLOUD_URL . '/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.soundcloud.com/me';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // handle HTTP Status errors
        if ($response->getStatusCode() >= 500) {
            throw new SoundCloudIdentityProviderException('Server Error: ' . $response->getReasonPhrase(), $response->getStatusCode(), (string) $response->getBody());
        }

        if ($response->getStatusCode() >= 400) {
            throw new SoundCloudIdentityProviderException('Client Error: ' . $response->getReasonPhrase(), $response->getStatusCode(), (string) $response->getBody());
        }

        // Skip error checking if we get what we expected
        if (\array_key_exists('access_token', $data)) {
            return;
        }

        // handle errors in 'error' key
        if (!empty($data['error'])) {
            switch ($data['error']['type']) {
                case 'DataException':
                    $message = 'No data returned.';
                    break;
                default:
                    $message = $data['error']['type'] . ': ' . $data['error']['message'];
            }

            throw new SoundCloudIdentityProviderException($message, 0, $data);
        }

        // handle errors NOT in 'error' key
        if (\array_key_exists('wrong_code', $data)) {
            throw new SoundCloudIdentityProviderException('Wrong code.', 0, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new SoundCloudResourceOwner($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);
        if (mb_strpos($type, 'urlencoded') !== false || mb_strpos($type, 'text/html') !== false) {
            parse_str($content, $parsed);

            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (\UnexpectedValueException $e) {
            if (mb_strpos($type, 'json') !== false) {
                throw $e;
            }

            return $content; // @phpstan-ignore-line Abstract method defined return type array, but in their implementation it could also return string
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAccessTokenResponse(array $result): array
    {
        $result = parent::prepareAccessTokenResponse($result);
        if (\array_key_exists('expires', $result) && '0' === $result['expires']) {
            // the token never expires. set a very long time expiring token because we need to set it
            $result['expires_in'] = 50 * 31556952;
            unset($result['expires']);
        }

        return $result;
    }
}
