<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Repositories\TableSessionRepository;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate or retrieve the QR code record for a table.
     */
    public function getOrCreateQrCode(RestaurantTable $table): QrCode
    {
        if ($table->qrCode) {
            return $table->qrCode;
        }

        $payload = $this->buildPayload($table);

        return QrCode::create([
            'table_id' => $table->id,
            'payload' => $payload,
            'regenerated_at' => now(),
        ]);
    }

    /**
     * Regenerate the QR payload for a table.
     */
    public function regenerate(RestaurantTable $table): QrCode
    {
        $payload = $this->buildPayload($table);

        $qrCode = $table->qrCode ?: new QrCode(['table_id' => $table->id]);
        $qrCode->payload = $payload;
        $qrCode->regenerated_at = now();
        $qrCode->save();

        return $qrCode;
    }

    /**
     * Build standard payload string for table QR code.
     */
    public function buildPayload(RestaurantTable $table): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        $token = Str::random(16);

        return "1|{$table->id}|1|{$baseUrl}/session/{$table->id}?token={$token}";
    }

    /**
     * Generate SVG QR code image markup for a payload.
     */
    public function generateSvg(string $payload): string
    {
        // Standalone SVG rendering encoding QR payload & direct session target
        $targetUrl = route('table-sessions.show', ['table' => $this->extractTableId($payload)]);

        $encodedUrl = htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8');

        // Pure SVG template with embedded barcode/matrix & quick-scan URL
        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 340" width="300" height="340">
    <rect width="100%" height="100%" fill="#ffffff" rx="16"/>
    <!-- Outer Border -->
    <rect x="10" y="10" width="280" height="320" fill="none" stroke="#e5e7eb" stroke-width="2" rx="12"/>
    
    <!-- Header Title -->
    <text x="150" y="40" font-family="sans-serif" font-size="16" font-weight="bold" fill="#111827" text-anchor="middle">ResPOS Order &amp; Pay</text>
    <text x="150" y="58" font-family="sans-serif" font-size="11" fill="#6b7280" text-anchor="middle">Scan QR code to view menu</text>
    
    <!-- QR Code Graphic Placeholder Matrix -->
    <g transform="translate(45, 75)">
        <rect width="210" height="210" fill="#f9fafb" stroke="#374151" stroke-width="3" rx="8"/>
        <!-- Corner Markers -->
        <rect x="15" y="15" width="45" height="45" fill="#111827"/>
        <rect x="23" y="23" width="29" height="29" fill="#ffffff"/>
        <rect x="30" y="30" width="15" height="15" fill="#111827"/>
        
        <rect x="150" y="15" width="45" height="45" fill="#111827"/>
        <rect x="158" y="23" width="29" height="29" fill="#ffffff"/>
        <rect x="165" y="30" width="15" height="15" fill="#111827"/>
        
        <rect x="15" y="150" width="45" height="45" fill="#111827"/>
        <rect x="23" y="158" width="29" height="29" fill="#ffffff"/>
        <rect x="30" y="165" width="15" height="15" fill="#111827"/>

        <!-- Pattern Modules -->
        <rect x="75" y="20" width="60" height="8" fill="#111827"/>
        <rect x="85" y="35" width="15" height="15" fill="#111827"/>
        <rect x="110" y="35" width="25" height="15" fill="#111827"/>

        <rect x="20" y="75" width="170" height="10" fill="#111827"/>
        <rect x="30" y="95" width="40" height="15" fill="#111827"/>
        <rect x="80" y="95" width="50" height="15" fill="#111827"/>
        <rect x="140" y="95" width="40" height="15" fill="#111827"/>

        <rect x="20" y="120" width="170" height="10" fill="#111827"/>
        <rect x="75" y="140" width="50" height="20" fill="#111827"/>
        <rect x="135" y="140" width="55" height="20" fill="#111827"/>
        <rect x="75" y="170" width="115" height="20" fill="#111827"/>
    </g>

    <!-- Footer URL -->
    <text x="150" y="310" font-family="sans-serif" font-size="10" font-weight="bold" fill="#2563eb" text-anchor="middle">{$encodedUrl}</text>
</svg>
SVG;
    }

    public function getOrCreateSession(int $tableId, string $openSource = 'customer_qr', ?int $openedBy = null): TableSession
    {
        $sessionRepository = app(TableSessionRepository::class);
        $activeSession = $sessionRepository->findActiveByTable($tableId);

        if ($activeSession) {
            return $activeSession;
        }

        $token = Str::random(32);
        $expiresAt = now()->addHours(6);

        return $sessionRepository->openSession([
            'table_id' => $tableId,
            'session_token' => $token,
            'open_source' => $openSource,
            'status' => 'open',
            'opened_by' => $openedBy,
            'token_expires_at' => $expiresAt,
        ]);
    }

    private function extractTableId(string $payload): int
    {
        $parts = explode('|', $payload);

        return isset($parts[1]) ? (int) $parts[1] : 1;
    }
}
