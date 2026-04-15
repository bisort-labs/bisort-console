<?php

declare(strict_types=1);

return [
    'validation' => [
        'email_address_in_use' => 'Email address is already in use.',
        'lost_reason_required' => 'Lost reason is required when a deal is marked as lost.',
        'vat_exemption_reason_required' => 'VAT exemption reason is required when a customer is marked as VAT exempt.',
    ],
    'notifications' => [
        'note_added' => 'Note added',
        'action_log_updated' => 'Timeline entry updated',
        'action_log_deleted' => 'Timeline entry deleted',
        'action_log_not_modified' => 'Timeline entry cannot be modified',
    ],
    'timeline' => [
        'untitled' => 'Untitled',
        'no_body_given' => 'No body was given',
        'no_actions_yet' => 'No actions yet.',
        'delete_action_log_confirmation' => 'Are you sure you want to delete this timeline entry?',
        'customer_created' => 'Customer created',
        'customer_created_body' => 'Customer record was created.',
        'customer_details_updated' => 'Customer details updated',
        'lead_details_updated' => 'Lead details updated',
        'deal_details_updated' => 'Deal details updated',
        'system' => 'System',
    ],
];
