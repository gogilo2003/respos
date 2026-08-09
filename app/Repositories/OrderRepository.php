<?php

namespace App\Repositories;

use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function createOrder(array $data)
    {
        return $this->model->create($data);
    }

    public function addOrderItem(array $data)
    {
        return OrderItem::create($data);
    }

    public function findById(int $id)
    {
        return $this->find($id);
    }

    public function updateStatus(int $id, string $status, ?array $timestamps = null)
    {
        $order = $this->find($id);

        if (! $order) {
            return false;
        }

        $updateData = ['status' => $status];

        if ($timestamps) {
            $updateData = array_merge($updateData, $timestamps);
        }

        return $order->update($updateData);
    }

    public function getOrdersBySessionIds(array $sessionIds)
    {
        return $this->model->whereIn('session_id', $sessionIds)->get();
    }
}
