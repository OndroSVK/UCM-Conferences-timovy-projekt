<?php

return [
    'greeting' => 'Dear :name,',
    'footer' => 'This message was sent automatically by the conference system. Please do not reply.',

    'registration_confirmation' => [
        'subject' => 'Registration received — :conference',
        'intro' => 'thank you for registering for :conference.',
        'pay' => 'Please transfer the conference fee using these details:',
        'no_payment' => 'No payment is required for your registration.',
        'user_page' => 'You can follow your registration, payment and submission on your user page.',
    ],

    'payment_received' => [
        'subject' => 'Payment received — :conference',
        'intro' => 'we have received your payment of :amount for :conference. Your fee is fully paid.',
        'overpaid' => 'You paid :amount more than the fee. The organizers will contact you about the refund.',
        'invoice_attached' => 'The test invoice is attached and also available on your user page.',
        'invoice_later' => 'The invoice will be available on your user page.',
    ],

    'submission_received' => [
        'subject' => 'Submission received — :conference',
        'intro' => 'we have received your submission ":title".',
        'new_version' => 'we have received version :version of your submission ":title".',
        'next' => 'The programme committee will inform you about its decision.',
    ],

    'decision' => [
        'subject' => 'Decision on your submission — :conference',
        'intro' => 'the programme committee has decided on your submission ":title":',
        'justification' => 'Comments of the committee:',
    ],

    'participation_confirmed' => [
        'subject' => 'Participation confirmed — :conference',
        'intro' => 'your participation in :conference is confirmed.',
        'details' => 'The conference takes place from :start to :end, :venue.',
        'accommodation' => 'Accommodation information:',
    ],
];
