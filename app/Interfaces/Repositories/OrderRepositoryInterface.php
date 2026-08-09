<?php

namespace App\Interfaces\Repositories;

interface OrderRepositoryInterface extends RepositoryInterface
{
    public function createOrder(array $data);

    public function addOrderItem(array $data);

    public function findById(int $id);

    public function updateStatus(int $id, string $status, ?array $timestamps = null);

    public function getOrdersBySessionIds(array $sessionIds);
}
