<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Create a daily database backup and overwrite the previous backup file';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $path = $backups->run();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Database backup overwritten: '.$path);

        return self::SUCCESS;
    }
}
