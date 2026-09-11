<?php

namespace Tests\Feature;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    public function test_backup_overwrites_the_same_file(): void
    {
        $source = storage_path('framework/testing/backup-source.sqlite');
        $relativePath = 'framework/testing/daily.sql';
        $destination = storage_path($relativePath);

        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'first-backup');
        File::delete($destination);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $source,
            'pm.backup.relative_path' => $relativePath,
        ]);

        $service = app(DatabaseBackupService::class);

        $this->assertSame($destination, $service->run());
        $this->assertSame('first-backup', File::get($destination));

        File::put($source, 'second-backup');

        $this->assertSame($destination, $service->run());
        $this->assertSame('second-backup', File::get($destination));
        $this->assertSame(1, collect(File::files(dirname($destination)))
            ->filter(fn ($file) => $file->getFilename() === 'daily.sql')
            ->count());
    }
}
