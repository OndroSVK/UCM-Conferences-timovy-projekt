<?php

/*
 * Enabled backend modules (backend.md 3.3). The order is the dependency order.
 * A disabled module must not break the others; this is tested for Invoicing (O32).
 */

return [
    'enabled' => [
        'Conferences' => true,
        'Auth' => true,
        'Workflow' => true,
        'Registration' => true,
        'Submissions' => true,
        'Payments' => true,
        'Invoicing' => (bool) env('MODULE_INVOICING_ENABLED', true),
        'ParticipantPortal' => true,
        'Notifications' => true,
        'Deadlines' => false, // full version (M10)
        'Reporting' => true,
    ],
];
