<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines when a product is considered to have low stock.
    | When stock quantity falls to or below this threshold, a notification
    | will be sent to the admin email.
    |
    */

    'low_stock_threshold' => env('CART_LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Admin Email
    |--------------------------------------------------------------------------
    |
    | This email address will receive notifications about low stock alerts
    | and daily sales reports.
    |
    */

    'admin_email' => env('CART_ADMIN_EMAIL', 'admin@example.com'),

];
