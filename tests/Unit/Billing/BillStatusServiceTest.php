<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Exceptions\InvalidBillTransitionException;
use App\Domain\Billing\Services\BillStatusService;
use PHPUnit\Framework\TestCase;

final class BillStatusServiceTest extends TestCase
{
    private BillStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BillStatusService();
    }

    public function test_it_allows_draft_to_open(): void
    {
        $this->assertSame(BillStatus::Open, $this->service->transition(BillStatus::Draft, BillStatus::Open));
    }

    public function test_it_allows_open_to_paid(): void
    {
        $this->assertSame(BillStatus::Paid, $this->service->transition(BillStatus::Open, BillStatus::Paid));
    }

    public function test_it_allows_open_to_voided(): void
    {
        $this->assertSame(BillStatus::Voided, $this->service->transition(BillStatus::Open, BillStatus::Voided));
    }

    public function test_it_allows_paid_to_refunded(): void
    {
        $this->assertSame(BillStatus::Refunded, $this->service->transition(BillStatus::Paid, BillStatus::Refunded));
    }

    public function test_it_allows_same_status(): void
    {
        $this->assertSame(BillStatus::Draft, $this->service->transition(BillStatus::Draft, BillStatus::Draft));
    }

    public function test_it_prevents_draft_to_paid(): void
    {
        $this->expectException(InvalidBillTransitionException::class);

        $this->service->transition(BillStatus::Draft, BillStatus::Paid);
    }

    public function test_it_prevents_open_to_draft(): void
    {
        $this->expectException(InvalidBillTransitionException::class);

        $this->service->transition(BillStatus::Open, BillStatus::Draft);
    }

    public function test_it_prevents_paid_to_open(): void
    {
        $this->expectException(InvalidBillTransitionException::class);

        $this->service->transition(BillStatus::Paid, BillStatus::Open);
    }

    public function test_it_prevents_paid_to_voided(): void
    {
        $this->expectException(InvalidBillTransitionException::class);

        $this->service->transition(BillStatus::Paid, BillStatus::Voided);
    }

    public function test_it_prevents_voided_to_paid(): void
    {
        $this->expectException(InvalidBillTransitionException::class);

        $this->service->transition(BillStatus::Voided, BillStatus::Paid);
    }
}
