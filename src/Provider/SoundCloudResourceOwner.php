<?php

declare(strict_types=1);

namespace Martin1982\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SoundCloudResourceOwner implements ResourceOwnerInterface
{
    protected array $data = [];

    public function __construct(array $response)
    {
        $this->data = $response;
    }

    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    public function getFirstname(): ?string
    {
        return $this->data['first_name'] ?? null;
    }

    public function getId(): string
    {
        return (string) $this->data['id'];
    }

    public function getLocale(): string
    {
        return $this->data['locale'];
    }

    public function getLastname(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    public function getUri(): ?string
    {
        return $this->data['uri'] ?? null;
    }

    public function getFullName(): ?string
    {
        return $this->data['full_name'] ?? null;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->data['avatar_url'] ?? null;
    }

    public function getOnline(): int
    {
        return $this->data['online'];
    }

    /**
     * Return all the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
