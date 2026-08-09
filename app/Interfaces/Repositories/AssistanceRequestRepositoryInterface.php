<?php

namespace App\Interfaces\Repositories;

interface AssistanceRequestRepositoryInterface extends RepositoryInterface
{
    public function getOpenRequests();

    public function getRequestsByStatus(string $status);
}
