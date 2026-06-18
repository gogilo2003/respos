<?php

namespace App\Interfaces\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;
}
