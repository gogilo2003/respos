<?php

namespace App\Repositories;

use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Models\RestaurantTable;

class TableRepository extends BaseRepository implements TableRepositoryInterface
{
    public function __construct(RestaurantTable $model)
    {
        parent::__construct($model);
    }

    public function getActiveTables()
    {
        return $this->model->where('is_active', true)
            ->orderBy('table_number')
            ->get();
    }

    public function findWithQrCode(int $id)
    {
        return $this->model->with('qrCode')->find($id);
    }

    public function getActiveTablesWithSessions()
    {
        return $this->model->where('is_active', true)
            ->with(['activeSession.orders', 'activeSession.assistanceRequests'])
            ->get();
    }
}
