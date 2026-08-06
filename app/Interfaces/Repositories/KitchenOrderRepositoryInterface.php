<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface KitchenOrderRepositoryInterface extends RepositoryInterface
{
    /**
     * Orders with at least one item in pending or accepted status.
     * Scoped to open sessions, ordered by placed_at ascending.
     */
    public function getPendingOrders(): Collection;

    /**
     * Orders with at least one item in preparing status.
     * Scoped to open sessions, ordered by placed_at ascending.
     */
    public function getPreparingOrders(): Collection;

    /**
     * Orders with at least one item in ready status (waiting to be served).
     * Scoped to open sessions, ordered by first_ready_at ascending.
     */
    public function getReadyOrders(): Collection;

    /**
     * Aggregate kitchen statistics for the current day.
     *
     * @return array{
     *     pending_items: int,
     *     preparing_items: int,
     *     ready_items: int,
     *     avg_prep_seconds: int|null,
     * }
     */
    public function getDailyStatistics(): array;
}
