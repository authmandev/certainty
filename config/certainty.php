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
    | Calculation Engine
    |--------------------------------------------------------------------------
    */

    'calculation_version' => 'cassandra_legacy_v1',

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
