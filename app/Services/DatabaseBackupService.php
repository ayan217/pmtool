<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function path(): string
    {
        return storage_path(config('pm.backup.relative_path'));
    }

    public function run(): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        File::ensureDirectoryExists(dirname($this->path()));

        return match ($config['driver'] ?? $connection) {
            'mysql', 'mariadb' => $this->backupMysql($config),
            'sqlite' => $this->backupSqlite($config),
            default => throw new RuntimeException("Backups are not supported for the [{$connection}] driver."),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function backupMysql(array $config): string
    {
        $binary = $this->findDumpBinary();
        $path = $this->path();
        $defaultsFile = $this->writeDefaultsFile($config);

        try {
            $process = new Process([
                $binary,
                '--defaults-extra-file='.$defaultsFile,
                '--single-transaction',
                '--routines',
                '--triggers',
                '--skip-comments',
                '--result-file='.$path,
                $config['database'],
            ]);

            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput().' '.$process->getOutput()) ?: 'mysqldump failed.');
            }
        } finally {
            File::delete($defaultsFile);
        }

        if (! File::exists($path) || File::size($path) === 0) {
            throw new RuntimeException('Backup file was not created.');
        }

        return $path;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function backupSqlite(array $config): string
    {
        $database = $config['database'] ?? null;

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            throw new RuntimeException('SQLite in-memory databases cannot be backed up to a file.');
        }

        if (! File::exists($database)) {
            throw new RuntimeException("SQLite database [{$database}] does not exist.");
        }

        File::copy($database, $this->path());

        return $this->path();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function writeDefaultsFile(array $config): string
    {
        $path = storage_path('app/backups/.mysqldump.cnf');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode(PHP_EOL, [
            '[client]',
            'host='.($config['host'] ?? '127.0.0.1'),
            'port='.($config['port'] ?? 3306),
            'user='.($config['username'] ?? 'root'),
            'password='.($config['password'] ?? ''),
            '',
        ]));

        return $path;
    }

    protected function findDumpBinary(): string
    {
        $configured = config('pm.backup.dump_binary');

        if (is_string($configured) && $configured !== '' && File::exists($configured)) {
            return $configured;
        }

        foreach ($this->binaryCandidates() as $candidate) {
            if ($candidate !== '' && (File::exists($candidate) || $this->binaryOnPath($candidate))) {
                return $candidate;
            }
        }

        throw new RuntimeException('mysqldump was not found. Set DB_DUMP_BINARY in .env to the full path.');
    }

    /**
     * @return list<string>
     */
    protected function binaryCandidates(): array
    {
        $candidates = ['mysqldump'];

        foreach (glob('C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysqldump.exe') ?: [] as $path) {
            $candidates[] = $path;
        }

        return $candidates;
    }

    protected function binaryOnPath(string $binary): bool
    {
        if (str_contains($binary, DIRECTORY_SEPARATOR) || str_contains($binary, '/')) {
            return false;
        }

        $process = Process::fromShellCommandline(PHP_OS_FAMILY === 'Windows' ? "where {$binary}" : "command -v {$binary}");
        $process->run();

        return $process->isSuccessful();
    }
}
