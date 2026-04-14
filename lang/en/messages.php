<?php

declare(strict_types=1);

return [
    'validation' => [
        'email_address_in_use' => 'Email address is already in use.',
    ],
    'notifications' => [
        'note_added' => 'Note added',
        'action_log_updated' => 'Timeline entry updated',
        'action_log_deleted' => 'Timeline entry deleted',
    ],
    'timeline' => [
        'untitled' => 'Untitled',
        'no_body_given' => 'No body was given',
        'no_actions_yet' => 'No actions yet.',
        'delete_action_log_confirmation' => 'Are you sure you want to delete this timeline entry?',
        'system' => 'System',
    ],
    'customers' => [
        'system_actions' => [
            'created' => [
                'title' => 'Customer created',
                'body' => 'The customer record was created.',
            ],
            'updated' => [
                'title' => 'Customer updated',
                'body' => 'The customer record was updated.',
            ],
        ],
    ],
];
