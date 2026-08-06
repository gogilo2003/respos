<?php

namespace App\Services;

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

            $bill = $this->billRepository->createBill([
                'session_id' => $session->id,
                'generated_by' => $userId,
                'status' => 'open',
            ]);

            $servedItems = OrderItem::where('session_id', $session->id)
                ->where('status', 'served')
                ->with('menuItem')
                ->get();

            $subtotal = 0;
            foreach ($servedItems as $item) {
                $unitPrice = $item->unit_price ?? $item->menuItem->base_price;
                $lineTotal = $unitPrice * $item->quantity;
                $this->billRepository->addBillItem($bill, [
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'served_at' => $item->served_at ?? now(),
                ]);
                $subtotal += $lineTotal;
            }

            $vatRate = 0;
            $vatAmount = 0;
            $serviceChargeRate = 0;
            $serviceChargeAmount = 0;

            $grandTotal = $subtotal + $vatAmount + $serviceChargeAmount;

            $this->billRepository->updateBill($bill, [
                'subtotal' => $subtotal,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge_amount' => $serviceChargeAmount,
                'grand_total' => $grandTotal,
            ]);

            return $bill->fresh('items');
        });
    }

    public function splitBillEqually(Bill $bill, int $numberOfSplits): array
    {
        $splits = [];
        $perPerson = $bill->grand_total / $numberOfSplits;

        for ($i = 0; $i < $numberOfSplits; $i++) {
            $splits[] = $this->billRepository->createSplit($bill, [
                'split_type' => 'equally',
                'amount_due' => $perPerson,
                'split_label' => 'Person ' . ($i + 1),
            ]);
        }

        return $splits;
    }

    public function splitBillByItem(Bill $bill, array $itemGroups): array
    {
        $splits = [];

        foreach ($itemGroups as $index => $itemIds) {
            $items = $bill->items()->whereIn('order_item_id', $itemIds)->get();
            $amount = $items->sum('line_total');

            $splits[] = $this->billRepository->createSplit($bill, [
                'split_type' => 'by_item',
                'amount_due' => $amount,
                'split_label' => 'Group ' . ($index + 1),
            ]);
        }

        return $splits;
    }

    public function splitBillCustom(Bill $bill, array $customAmounts): array
    {
        $splits = [];

        foreach ($customAmounts as $index => $amount) {
            $splits[] = $this->billRepository->createSplit($bill, [
                'split_type' => 'custom',
                'amount_due' => $amount,
                'split_label' => 'Custom ' . ($index + 1),
            ]);
        }

        return $splits;
    }

    public function processPayment(Bill $bill, float $amountReceived, int $cashierId): array
    {
        $remainingAmount = $bill->splits()->where('status', 'unpaid')->sum('amount_due');

        if ($amountReceived > $remainingAmount) {
            $changeDue = $amountReceived - $remainingAmount;
            $amountToApply = $remainingAmount;
        } else {
            $changeDue = 0;
            $amountToApply = $amountReceived;
        }

        $payment = $bill->payments()->create([
            'cashier_id' => $cashierId,
            'payment_method' => 'cash',
            'amount_due' => $amountToApply,
            'amount_received' => $amountReceived,
            'change_due' => $changeDue,
        ]);

        $bill->splits()->where('status', 'unpaid')->orderBy('id')->chunkById(100, function ($splits) use ($amountToApply, $payment) {
            $remaining = $amountToApply;

            foreach ($splits as $split) {
                if ($remaining <= 0) break;

                if ($split->status === 'paid') continue;

                $applyAmount = min($split->amount_due - $split->amount_paid, $remaining);

                $split->increment('amount_paid', $applyAmount);
                $remaining -= $applyAmount;

                if ($split->amount_paid >= $split->amount_due) {
                    $split->update(['status' => 'paid']);
                } elseif ($split->amount_paid > 0) {
                    $split->update(['status' => 'partially_paid']);
                }
            }
        });

        $remainingBalance = $bill->splits()->where('status', 'unpaid')->sum('amount_due');
        if ($remainingBalance == 0) {
            $bill->update(['status' => 'paid', 'paid_at' => now()]);
        } else {
            $bill->update(['status' => 'partially_paid']);
        }

        return [
            'payment' => $payment,
            'change_due' => $changeDue,
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
