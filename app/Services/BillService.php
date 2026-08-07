<?php

namespace App\Services;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Interfaces\Repositories\BillRepositoryInterface;
use App\Models\Bill;
use App\Models\OrderItem;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;

class BillService
{
    protected BillRepositoryInterface $billRepository;

    public function __construct(BillRepositoryInterface $billRepository)
    {
        $this->billRepository = $billRepository;
    }

    public function generateBill(int $sessionId, int $userId): Bill
    {
        return DB::transaction(function () use ($sessionId, $userId) {
            $session = TableSession::findOrFail($sessionId);

            $billData = BillData::from(
                billNumber: 'BILL-' . $session->id . '-' . time(),
                customer: $session->table?->table_number ?? null,
                table: $session->table?->table_number ?? null,
                order: null,
                items: [],
                subtotal: 0,
                discount: 0,
                tax: 0,
                serviceCharge: 0,
                grandTotal: 0,
                status: BillStatus::Open,
                createdAt: new \DateTimeImmutable(),
                sessionId: $session->id,
                generatedBy: $userId,
                discountApprovedBy: null,
                discountReason: null,
                voidedBy: null,
                voidReason: null,
                paidAt: null,
                voidedAt: null,
            );

            $billData = $this->billRepository->create($billData);

            $servedItems = OrderItem::where('session_id', $session->id)
                ->where('status', 'served')
                ->with('menuItem')
                ->get();

            $subtotal = 0;
            foreach ($servedItems as $item) {
                $unitPrice = $item->unit_price ?? $item->menuItem->base_price;
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;
            }

            $vatRate = 0;
            $vatAmount = 0;
            $serviceChargeRate = 0;
            $serviceChargeAmount = 0;

            $grandTotal = $subtotal + $vatAmount + $serviceChargeAmount;

            $billData = $this->billRepository->update(BillData::from(
                billNumber: $billData->billNumber,
                customer: $billData->customer,
                table: $billData->table,
                order: $billData->order,
                items: $billData->items(),
                subtotal: $subtotal,
                discount: 0,
                tax: $vatAmount,
                serviceCharge: $serviceChargeAmount,
                grandTotal: $grandTotal,
                status: BillStatus::Open,
                createdAt: $billData->createdAt,
                sessionId: $session->id,
                generatedBy: $userId,
                discountApprovedBy: null,
                discountReason: null,
                voidedBy: null,
                voidReason: null,
                paidAt: null,
                voidedAt: null,
            ));

            return Bill::findOrFail((int) str_replace('BILL-', '', explode('-', $billData->billNumber)[1] ?? $billData->billNumber));
        });
    }

    public function splitBillEqually(Bill $bill, int $numberOfSplits): array
    {
        $splits = [];
        $perPerson = $bill->grand_total / $numberOfSplits;

        for ($i = 0; $i < $numberOfSplits; $i++) {
            // Split persistence is not yet moved to the new repository contract.
            $splits[] = $bill;
        }

        return $splits;
    }

    public function splitBillByItem(Bill $bill, array $itemGroups): array
    {
        $splits = [];

        foreach ($itemGroups as $index => $itemIds) {
            // Split persistence is not yet moved to the new repository contract.
            $splits[] = $bill;
        }

        return $splits;
    }

    public function splitBillCustom(Bill $bill, array $customAmounts): array
    {
        $splits = [];

        foreach ($customAmounts as $index => $amount) {
            // Split persistence is not yet moved to the new repository contract.
            $splits[] = $bill;
        }

        return $splits;
    }

    public function processPayment(Bill $bill, float $amountReceived, int $cashierId): array
    {
        // Payment persistence is not yet moved to the new repository contract.
        return [
            'payment' => null,
            'change_due' => 0,
            'status' => $bill->status,
        ];
    }

    public function generateReceipt(Bill $bill): string
    {
        $receiptLines = [];
        $receiptLines[] = '===== Restaurant Receipt =====';
        $receiptLines[] = 'Bill: #' . $bill->id;
        $receiptLines[] = 'Table: ' . ($bill->session?->table?->table_number ?? 'N/A');
        $receiptLines[] = 'Date: ' . $bill->generated_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i');
        $receiptLines[] = 'Items:';
        foreach ($bill->items as $item) {
            $receiptLines[] = '- ' . $item->orderItem->menuItem->name . ' (' . $item->quantity . ') @ ' . $item->unit_price;
        }
        $receiptLines[] = 'Subtotal: ' . number_format($bill->subtotal, 2);
        $receiptLines[] = 'VAT: ' . number_format($bill->vat_amount, 2);
        $receiptLines[] = 'Service Charge: ' . number_format($bill->service_charge_amount, 2);
        $receiptLines[] = 'Discount: ' . number_format($bill->discount_amount, 2);
        $receiptLines[] = 'Total: ' . number_format($bill->grand_total, 2);
        $receiptLines[] = '===== Thank You! =====';

        return implode("\n", $receiptLines);
    }
}
