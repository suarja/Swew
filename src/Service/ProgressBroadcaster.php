<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class ProgressBroadcaster
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function broadcastAssignment(User $user, array $payload): void
    {
        $topic = sprintf('assignments/%d/%s', $user->getId(), $payload['assignment'] ?? 'unknown');
        $this->hub->publish(new Update($topic, json_encode($payload, JSON_THROW_ON_ERROR)));
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function broadcastProgress(User $user, array $snapshot): void
    {
        $topic = sprintf('progress/%d', $user->getId());
        $this->hub->publish(new Update($topic, json_encode($snapshot, JSON_THROW_ON_ERROR)));
    }
}
