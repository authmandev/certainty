<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Study Pricing
    |--------------------------------------------------------------------------
    |
    | The price charged per feasibility study. The amount is copied onto each
    | study's payment record when the study is created, so changing it later
    | never affects existing studies (BRD A-04).
    |
    */

    'study_price' => (float) env('CERTAINTY_STUDY_PRICE', 1200),

    'currency' => env('CERTAINTY_CURRENCY', 'SAR'),

    /*
    |--------------------------------------------------------------------------
    | Draft Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of days a customer has to complete a draft study after creating
    | it (BRD A-01).
    |
    */

    'draft_duration_days' => (int) env('CERTAINTY_DRAFT_DURATION_DAYS', 60),

    /*
    |--------------------------------------------------------------------------
    | Study Currencies
    |--------------------------------------------------------------------------
    |
    | ISO 4217 codes a customer may choose for a study's figures. The first
    | entry is the default for new studies.
    |
    */

    'study_currencies' => [
        'SAR' => 'ريال سعودي',
        'KWD' => 'دينار كويتي',
        'AED' => 'درهم إماراتي',
        'BHD' => 'دينار بحريني',
        'QAR' => 'ريال قطري',
        'OMR' => 'ريال عماني',
        'EGP' => 'جنيه مصري',
        'JOD' => 'دينار أردني',
        'IQD' => 'دينار عراقي',
        'LBP' => 'ليرة لبنانية',
        'SYP' => 'ليرة سورية',
        'YER' => 'ريال يمني',
        'SDG' => 'جنيه سوداني',
        'LYD' => 'دينار ليبي',
        'TND' => 'دينار تونسي',
        'DZD' => 'دينار جزائري',
        'MAD' => 'درهم مغربي',
        'USD' => 'دولار أمريكي',
        'EUR' => 'يورو',
        'GBP' => 'جنيه إسترليني',
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculation Engine
    |--------------------------------------------------------------------------
    */

    'calculation_version' => 'cassandra_legacy_v1',

    /*
    |--------------------------------------------------------------------------
    | Bank Transfer
    |--------------------------------------------------------------------------
    |
    | The account customers transfer the study fee to (BRD FR-017, FR-018).
    | Leave the values empty until the real account is known; the study page
    | then asks the customer to contact support for the details.
    |
    */

    'bank' => [
        'name' => env('CERTAINTY_BANK_NAME'),
        'account_name' => env('CERTAINTY_BANK_ACCOUNT_NAME'),
        'iban' => env('CERTAINTY_BANK_IBAN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Receipts
    |--------------------------------------------------------------------------
    |
    | Receipts are stored on a private disk (BRD FR-020, NFR-003).
    |
    */

    'receipts' => [
        'disk' => env('CERTAINTY_RECEIPTS_DISK', 'local'),
        'max_kilobytes' => (int) env('CERTAINTY_RECEIPT_MAX_KB', 5120),
        'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Contact Details
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'email' => env('CERTAINTY_CONTACT_EMAIL'),
        'phone' => env('CERTAINTY_CONTACT_PHONE'),
    ],

];
