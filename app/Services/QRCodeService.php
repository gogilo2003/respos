<?php

namespace App\Services;

use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Repositories\TableSessionRepository;

class QRCodeService
{
    protected TableSessionRepository $sessionRepository;

    public function __construct(TableSessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function buildPayload(int $tableId, ?string $baseUrl = null): string
    {
        $baseUrl = $baseUrl ?? config('app.url');
        $restaurantId = 1;

        return sprintf('%d|%d|%s', $restaurantId, $tableId, rtrim($baseUrl, '/'));
    }

    public function parsePayload(string $payload): ?array
    {
        $parts = explode('|', $payload);

        if (count($parts) !== 3) {
            return null;
        }

        return [
            'restaurant_id' => (int) $parts[0],
            'table_id' => (int) $parts[1],
            'base_url' => $parts[2],
        ];
    }

    public function validatePayload(string $payload): ?array
    {
        $parsed = $this->parsePayload($payload);

        if (! $parsed) {
            return null;
        }

        $table = RestaurantTable::where('id', $parsed['table_id'])
            ->where('is_active', true)
            ->first();

        if (! $table) {
            return null;
        }

        return array_merge($parsed, ['table' => $table]);
    }

    public function getOrCreateSession(int $tableId, string $openSource = 'customer_qr', ?int $openedBy = null): TableSession
    {
        $activeSession = $this->sessionRepository->findActiveByTable($tableId);

        if ($activeSession) {
            return $activeSession;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addDays(3);

        return $this->sessionRepository->openSession([
            'table_id' => $tableId,
            'session_token' => $token,
            'open_source' => $openSource,
            'status' => 'open',
            'opened_by' => $openedBy,
            'token_expires_at' => $expiresAt,
        ]);
    }
}
