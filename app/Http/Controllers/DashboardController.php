<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $payload = $this->dashboardService->getDashboardPayload($request->user());

        if (isset($payload['redirect'])) {
            return redirect($payload['redirect']);
        }

        return Inertia::render($payload['component'], $payload['props']);
    }
}
