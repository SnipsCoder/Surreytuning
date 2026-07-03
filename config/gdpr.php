<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Data-subject export storage
    |--------------------------------------------------------------------------
    |
    | Disk + folder where `gdpr:export` writes the machine-readable data
    | packages. Kept on a private local disk by default — these files contain
    | the full personal data of a data subject and must never be public.
    |
    */

    'export_disk' => env('GDPR_EXPORT_DISK', 'local'),
    'export_path' => 'gdpr-exports',

    /*
    |--------------------------------------------------------------------------
    | Retention windows (days)
    |--------------------------------------------------------------------------
    |
    | Storage-limitation policy (GDPR Art. 5(1)(e)) applied by `gdpr:prune`.
    | Financial records (invoices, credit ledger) are NOT pruned here — they
    | are retained for the statutory accounting period and only anonymised on
    | an explicit erasure request.
    |
    */

    'retention' => [
        // Rejected dealer applications that never became a customer.
        'rejected_applications_days' => (int) env('GDPR_RETAIN_REJECTED_APPLICATIONS_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legal / contact details for the published policies
    |--------------------------------------------------------------------------
    */

    'privacy_contact_email' => env('GDPR_PRIVACY_CONTACT_EMAIL'),

];
