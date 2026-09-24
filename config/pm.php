<?php

return [
    'reminder_hours' => [1, 2, 3, 4, 6, 12, 24],
    'default_reminder_hours' => 3,
    'daily_reminder_time' => '18:00',
    'per_page_options' => [25, 50, 100],
    'default_per_page' => 25,
    'default_deadline_time' => '23:59',

    'seed' => [
        'name' => env('SEED_USER_NAME', 'Ayan'),
        'email' => env('SEED_USER_EMAIL', 'ayan@example.com'),
        'password' => env('SEED_USER_PASSWORD', 'password'),
    ],

    'backup' => [
        'relative_path' => env('DB_BACKUP_PATH', 'app/backups/daily.sql'),
        'dump_binary' => env('DB_DUMP_BINARY'),
    ],

    'attachments' => [
        'disk' => env('FILESYSTEM_DISK', 'local'),
        'max_files' => 20,
        'max_kilobytes' => 25600,
        'blocked_extensions' => ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'ps1', 'cgi', 'htaccess'],
    ],

    'status_reminder' => [
        'subject' => '{task.title}',
        'body' => "Task: {task.title}\nProject: {project.name}\n\nhi team,\n\nplease share the status of this task, the deadline is in {remaining.hours}, if any delay is happening please contact me personally over call or whatsapp.\n\n{task.des}",
    ],
];
