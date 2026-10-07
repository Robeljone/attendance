<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Network Allowlist
    |--------------------------------------------------------------------------
    |
    | Employees must be on the company network to clock in/out via QR. Browsers
    | cannot read Wi-Fi SSIDs, so access is enforced by client IP / CIDR ranges
    | that match your office Wi-Fi / LAN egress addresses.
    |
    */

    'enforce_company_network' => env('ATTENDANCE_ENFORCE_NETWORK', true),

    'allowed_ip_cidrs' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ATTENDANCE_ALLOWED_IP_CIDRS', '127.0.0.1/32,::1/128'))
    ))),

    'qr_token_ttl_seconds' => (int) env('ATTENDANCE_QR_TTL', 60),

    'default_work_hours_per_day' => 8,

    'overtime_multiplier' => (float) env('ATTENDANCE_OVERTIME_MULTIPLIER', 1.5),

];
