<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Mercure\Update;

final class NullHub implements HubInterface
{
    private TokenProviderInterface $provider;

    public function __construct()
    {
        $this->provider = new class() implements TokenProviderInterface {
            public function getJwt(): string
            {
                return 'test';
            }
        };
    }

    public function getUrl(): string
    {
        return 'http://localhost/.well-known/mercure';
    }

    public function getPublicUrl(): string
    {
        return $this->getUrl();
    }

    public function getProvider(): TokenProviderInterface
    {
        return $this->provider;
    }

    public function getFactory(): ?TokenFactoryInterface
    {
        return null;
    }

    public function publish(Update $update): string
    {
        return 'null';
    }
}
