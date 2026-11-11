<?php

/*
 * Domain settings that are not stored per conference.
 * Prices, IBAN and deadlines are always per conference in the database (R32).
 */

return [
    'login' => [
        // PROPOSAL: BO2 — working limit 3 failed attempts, to be confirmed with the team.
        'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 3),
        'decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 300),
    ],

    'email_check' => [
        // CheckEmailRegistered reveals whether an account exists (US-04); limited per IP.
        'max_attempts' => (int) env('EMAIL_CHECK_MAX_ATTEMPTS', 10),
        'decay_seconds' => 60,
    ],

    'submissions' => [
        // PROPOSAL: BO5 — 10 MB limit.
        'max_kilobytes' => (int) env('SUBMISSION_MAX_KB', 10240),
        'disk' => 'local',
    ],

    'invoices' => [
        'disk' => 'local',
    ],

    // Development seed accounts only; never real people (backend.md 4.7).
    'seed_password' => env('SEED_USER_PASSWORD', 'password'),
];
