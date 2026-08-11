<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Repositories\TableSessionRepository;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode as ChillerlanQRCode;
use chillerlan\QRCode\QROptions;
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
        $tableId = $this->extractTableId($payload);
        $targetUrl = route('session.entry', ['table' => $tableId]);

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'svgDefs' => '',
        ]);

        return (new ChillerlanQRCode($options))->render($targetUrl);
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
