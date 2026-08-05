<?php

namespace App\Interfaces\Repositories;

interface TableSessionRepositoryInterface extends RepositoryInterface
{
    public function findActiveByTable(int $tableId);

    public function openSession(array $data);

    public function closeSession(int $id, ?int $closedBy = null, ?string $closeReason = null);
}
