<?php

namespace App\Service;

use App\Repository\ApiTokenRepository;
use App\Repository\DeviceLoginRequestRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;

class DashboardMetrics
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly DeviceLoginRequestRepository $deviceLoginRequestRepository,
    ) {
    }

    public function snapshot(): array
    {
        $now = new DateTimeImmutable();

        $totalUsers = $this->userRepository->count([]);
        $activeTokens = $this->apiTokenRepository->countActiveTokens($now);
        $pendingDeviceApprovals = $this->deviceLoginRequestRepository->countPendingApprovals($now);

        return [
            'totalUsers' => $totalUsers,
            'activeTokens' => $activeTokens,
            'pendingDeviceApprovals' => $pendingDeviceApprovals,
            'systemStatus' => $this->determineStatus($pendingDeviceApprovals, $activeTokens),
        ];
    }

    private function determineStatus(int $pendingDeviceApprovals, int $activeTokens): string
    {
        if ($pendingDeviceApprovals > 0) {
            return 'device approvals pending';
        }

        if ($activeTokens === 0) {
            return 'waiting for first token';
        }

        return 'queue · idle';
    }
}
