<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Interfaces\Repositories\TableSessionRepositoryInterface;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableSessionController extends Controller
{
    protected TableSessionRepositoryInterface $sessionRepository;

    protected TableRepositoryInterface $tableRepository;

    protected QRCodeService $qrCodeService;

    public function __construct(
        TableSessionRepositoryInterface $sessionRepository,
        TableRepositoryInterface $tableRepository,
        QRCodeService $qrCodeService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->tableRepository = $tableRepository;
        $this->qrCodeService = $qrCodeService;
    }

    public function show(Request $request, string $identifier)
    {
        $tableId = null;

        if (str_contains($identifier, '|')) {
            $validated = $this->qrCodeService->validatePayload($identifier);

            if (! $validated || ! isset($validated['table'])) {
                return Inertia::render('Tables/InvalidQr');
            }

            $tableId = (int) $validated['table_id'];
        } else {
            $tableId = (int) $identifier;
        }

        $table = $this->tableRepository->find($tableId);

        if (! $table || ! $table->is_active) {
            return Inertia::render('Tables/InvalidQr');
        }

        $session = $this->qrCodeService->getOrCreateSession($tableId, 'customer_qr');

        $baseUrl = url('/');

        return Inertia::render('Tables/Session', [
            'table' => $table,
            'session' => $session,
            'qr_payload' => $this->qrCodeService->buildPayload($tableId, $baseUrl),
            'menu_url' => route('welcome.menu'),
        ]);
    }

    public function close(Request $request, int $id)
    {
        $validated = $request->validate([
            'close_reason' => 'nullable|string|max:200',
        ]);

        $session = $this->sessionRepository->closeSession(
            $id,
            $request->user()?->id,
            $validated['close_reason'] ?? null
        );

        if (! $session) {
            return redirect()->back()->with('error', 'Session not found.');
        }

        return redirect()->back()->with('message', 'Table session closed successfully.');
    }
}
