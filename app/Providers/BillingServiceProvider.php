<?php

namespace App\Providers;

use App\Repositories\BillRepository;
use App\Repositories\Contracts\BillRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BillRepositoryInterface::class,
            BillRepository::class
        );
    }
}
