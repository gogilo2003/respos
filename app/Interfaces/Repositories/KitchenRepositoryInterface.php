<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * All persistence queries for the kitchen domain.
 *
 * No business logic lives here — only data retrieval and writes
 * that the kitchen controller and dashboard service delegate to.
 */
interface KitchenRepositoryInterface extends RepositoryInterface
{
    // -------------------------------------------------------------------------
    // Dashboard order lists
    // -------------------------------------------------------------------------

    /**
     * Orders with at least one item in pending or accepted status,
     * scoped to open sessions and ordered by placed_at ascending.
     */
    public function getPendingOrders(): Collection;

    /**
     * Orders with at least one item in preparing status,
     * scoped to open sessions and ordered by placed_at ascending.
     */
    public function getPreparingOrders(): Collection;

    /**
     * Orders with at least one item in ready status (awaiting delivery),
     * scoped to open sessions and ordered by first_ready_at ascending.
     */
    public function getReadyOrders(): Collection;

    // -------------------------------------------------------------------------
    // Statistics
    // -------------------------------------------------------------------------

    /**
     * Live item-level counts for the current shift plus today's average
     * preparation time in seconds.
     *
     * @return array{
     *     pending_items: int,
     *     preparing_items: int,
     *     ready_items: int,
     *     avg_prep_seconds: int|null,
     * }
     */
    public function getDailyStatistics(): array;

    // -------------------------------------------------------------------------
    // Item-status mutation support
    // -------------------------------------------------------------------------

    /**
     * Reload an order's items relationship from the database.
     * Used inside transactions after an item-status update to get
     * the authoritative post-write state without a full model re-fetch.
     */
    public function refreshOrderItems(Order $order): Order;

    /**
     * Set the status column of an order record directly.
     * Does not touch timestamps — callers are responsible for those via
     * OrderService when a full status-change workflow is needed.
     */
    public function setOrderStatus(Order $order, string $status): void;
}
