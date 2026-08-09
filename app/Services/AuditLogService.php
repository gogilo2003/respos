<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log a security or critical operation.
     */
    public function log(
        string $action,
        string $entityType,
        int|string $entityId,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $reason = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?: auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
