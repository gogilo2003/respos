<?php

namespace App\Interfaces\Repositories;

interface TableRepositoryInterface extends RepositoryInterface
{
    public function getActiveTables();

    public function findWithQrCode(int $id);
}
