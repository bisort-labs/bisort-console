<?php

declare(strict_types=1);

return [
    'validation' => [
        'email_address_in_use' => 'Diese E-Mail-Adresse wird bereits verwendet.',
    ],
    'notifications' => [
        'note_added' => 'Notiz hinzugefügt',
        'action_log_updated' => 'Zeitachsen-Eintrag aktualisiert',
        'action_log_deleted' => 'Zeitachsen-Eintrag gelöscht',
    ],
    'timeline' => [
        'untitled' => 'Ohne Titel',
        'no_body_given' => 'Es wurde kein Inhalt angegeben.',
        'no_actions_yet' => 'Noch keine Aktionen vorhanden.',
        'delete_action_log_confirmation' => 'Möchtest du diesen Zeitachsen-Eintrag wirklich löschen?',
        'system' => 'System',
    ],
    'customers' => [
        'system_actions' => [
            'created' => [
                'title' => 'Kunde erstellt',
                'body' => 'Der Kundendatensatz wurde erstellt.',
            ],
            'updated' => [
                'title' => 'Kunde aktualisiert',
                'body' => 'Der Kundendatensatz wurde aktualisiert.',
            ],
        ],
    ],
];
