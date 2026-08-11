<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency code and display symbol used across billing outputs.
    |
    */

    'currency' => env('BILLING_CURRENCY', 'KES'),

    'currency_symbol' => env('BILLING_CURRENCY_SYMBOL', 'KES '),

    /*
    |--------------------------------------------------------------------------
    | Tax Defaults
    |--------------------------------------------------------------------------
    |
    | Default tax rate and type applied when no explicit tax configuration
    | is provided for a bill.
    |
    */

    'tax' => [
        'default_rate' => env('BILLING_TAX_RATE', 0.00),
        'default_type' => env('BILLING_TAX_TYPE', 'exclusive'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Charge Defaults
    |--------------------------------------------------------------------------
    |
    | Default service charge rate applied when none is specified.
    |
    */

    'service_charge' => [
        'default_rate' => env('BILLING_SERVICE_CHARGE_RATE', 0.00),
    ],

    /*
    |--------------------------------------------------------------------------
    | Numbering Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes used for bill and receipt numbering.
    |
    */

    'bill_prefix' => env('BILLING_BILL_PREFIX', 'BILL'),

    'receipt_prefix' => env('BILLING_RECEIPT_PREFIX', 'RCP'),

    /*
    |--------------------------------------------------------------------------
    | Rounding
    |--------------------------------------------------------------------------
    |
    | Default rounding mode for monetary calculations.
    |
    */

    'default_rounding' => env('BILLING_DEFAULT_ROUNDING', 'half_up'),
];
