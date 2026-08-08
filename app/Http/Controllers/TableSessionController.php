<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Interfaces\Repositories\TableSessionRepositoryInterface;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TableSessionController extends Controller
{
    public function __construct(
        protected TableSessionRepositoryInterface $sessionRepository,
        protected TableRepositoryInterface $tableRepository
    ) {}

    public function show(Request $request, string $identifier)
    {
        $tableId = (int) $identifier;
        $table = RestaurantTable::find($tableId);

        if (! $table || ! $table->is_active) {
            return Inertia::render('Tables/InvalidQr');
        }

        // Get open session or create a new active customer QR session
        $session = TableSession::where('table_id', $table->id)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            $session = TableSession::create([
                'table_id' => $table->id,
                'opened_by' => null,
                'session_token' => Str::random(32),
                'token_expires_at' => now()->addHours(6),
                'status' => 'open',
                'opened_at' => now(),
            ]);
        }

        // Store active session token & table number in session
        session(['active_session_id' => $session->id, 'active_table_number' => $table->table_number]);

        return Inertia::render('Tables/Session', [
            'table' => $table,
            'session' => $session,
            'menu_url' => route('menu'),
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
