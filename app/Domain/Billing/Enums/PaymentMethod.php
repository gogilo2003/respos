<?php

namespace App\Domain\Billing\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Mpesa = 'mpesa';
    case BankTransfer = 'bank_transfer';
    case Voucher = 'voucher';
}
