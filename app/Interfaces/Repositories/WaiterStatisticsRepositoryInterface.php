<?php

namespace App\Interfaces\Repositories;

interface WaiterStatisticsRepositoryInterface extends RepositoryInterface
{
    public function getOccupiedTableCount(): int;

    public function getPendingOrderCount(array $sessionIds): int;

    public function getReadyOrderCount(array $sessionIds): int;

    public function getCompletedTodayCount(array $sessionIds): int;

    public function getOpenAssistanceRequestCount(): int;
}
