<?php

declare(strict_types=1);

/**
 * Spinnin' Platform - All rights reserved.
 */

namespace Martin1982\OAuth2\Client\Test\Provider;

use Martin1982\OAuth2\Client\Provider\SoundCloudResourceOwner;
use PHPUnit\Framework\TestCase;

/**
 * Class SoundCloudResourceOwnerTest.
 *
 * @internal
 *
 * @coversNothing
 */
class SoundCloudResourceOwnerTest extends TestCase
{
    protected SoundCloudResourceOwner $resourceOwner;

    protected function setUp(): void
    {
        $user = json_decode(file_get_contents(__DIR__ . '/../fixtures/user.json'), true);
        $this->resourceOwner = new SoundCloudResourceOwner($user);
    }

    public function testGetter(): void
    {
        $this->assertSame('0', $this->resourceOwner->getId());
        $this->assertSame('https://image.img/avatar.png', $this->resourceOwner->getAvatarUrl());
        $this->assertSame('NL', $this->resourceOwner->getCountry());
        $this->assertSame('john', $this->resourceOwner->getFirstname());
        $this->assertSame('doe', $this->resourceOwner->getLastname());
        $this->assertSame('john doe', $this->resourceOwner->getFullName());
        $this->assertSame('BE_nl', $this->resourceOwner->getLocale());
        $this->assertTrue($this->resourceOwner->getOnline());
        $this->assertSame('https://some.uri', $this->resourceOwner->getUri());
    }

    public function testToArray(): void
    {
        $array = json_decode(file_get_contents(__DIR__ . '/../fixtures/user.json'), true);

        $this->assertSame($array, $this->resourceOwner->toArray());
    }
}
