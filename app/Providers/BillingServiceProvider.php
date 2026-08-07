<?php

namespace App\Providers;

use App\Domain\Billing\Contracts\BillGeneratorInterface;
use App\Domain\Billing\Services\BillGenerator;
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

        $this->app->bind(
            BillGeneratorInterface::class,
            BillGenerator::class
        );
    }
}
