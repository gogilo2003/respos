<?php

namespace App\Repositories;

use App\Interfaces\Repositories\AssistanceRequestRepositoryInterface;
use App\Models\AssistanceRequest;

class AssistanceRequestRepository extends BaseRepository implements AssistanceRequestRepositoryInterface
{
    public function __construct(AssistanceRequest $model)
    {
        parent::__construct($model);
    }

    public function getOpenRequests()
    {
        return $this->model->with('session.table')
            ->where('status', 'open')
            ->orderByDesc('requested_at')
            ->get();
    }

    public function getRequestsByStatus(string $status)
    {
        return $this->model->with('session.table')
            ->where('status', $status)
            ->orderByDesc('requested_at')
            ->get();
    }
}
