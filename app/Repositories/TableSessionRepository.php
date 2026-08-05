<?php

namespace App\Repositories;

use App\Interfaces\Repositories\TableSessionRepositoryInterface;
use App\Models\TableSession;

class TableSessionRepository extends BaseRepository implements TableSessionRepositoryInterface
{
    public function __construct(TableSession $model)
    {
        parent::__construct($model);
    }

    public function findActiveByTable(int $tableId)
    {
        return $this->model->where('table_id', $tableId)
            ->whereIn('status', ['open', 'billing'])
            ->latest()
            ->first();
    }

    public function openSession(array $data)
    {
        return $this->model->create($data);
    }

    public function closeSession(int $id, ?int $closedBy = null, ?string $closeReason = null)
    {
        $session = $this->find($id);
        if (! $session) {
            return false;
        }

        $session->update([
            'status' => 'closed',
            'closed_by' => $closedBy,
            'close_reason' => $closeReason,
            'closed_at' => now(),
        ]);

        return $session;
    }
}
