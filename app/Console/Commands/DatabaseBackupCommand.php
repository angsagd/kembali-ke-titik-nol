<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'backup:database')]
#[Signature('backup:database {--connection= : Database connection name} {--disk=local : Filesystem disk} {--path=backups/database : Backup directory path}')]
#[Description('Create a database backup file for go-live readiness.')]
class DatabaseBackupCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connectionName = (string) ($this->option('connection') ?: config('database.default'));
        $disk = (string) $this->option('disk');
        $directory = trim((string) $this->option('path'), '/');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection)) {
            $this->error("Database connection [{$connectionName}] tidak ditemukan.");

            return self::FAILURE;
        }

        $driver = (string) ($connection['driver'] ?? '');
        $timestamp = now()->format('Ymd_His');
        $extension = $driver === 'sqlite' ? 'sql' : 'dump.sql';
        $filename = "{$directory}/{$connectionName}_{$timestamp}.{$extension}";

        try {
            $contents = $driver === 'sqlite'
                ? $this->sqliteDump($connectionName)
                : $this->externalDump($driver, $connection);

            Storage::disk($disk)->put($filename, $contents);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup database berhasil dibuat: {$filename}");

        return self::SUCCESS;
    }

    private function sqliteDump(string $connectionName): string
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $tables = DB::connection($connectionName)
            ->select("select name, sql from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name");
        $statements = [
            '-- Kembali ke Titik Nol database backup',
            '-- Connection: '.$connectionName,
            '-- Generated at: '.now()->toDateTimeString(),
            'PRAGMA foreign_keys=OFF;',
            'BEGIN TRANSACTION;',
        ];

        foreach ($tables as $table) {
            $tableName = (string) $table->name;
            $createSql = (string) $table->sql;

            if ($createSql !== '') {
                $statements[] = $createSql.';';
            }

            $columns = collect(DB::connection($connectionName)->select('PRAGMA table_info('.$this->quoteIdentifier($tableName).')'))
                ->pluck('name')
                ->map(fn (mixed $column): string => (string) $column)
                ->all();

            foreach (DB::connection($connectionName)
                ->table($tableName)
                ->orderBy($columns[0] ?? 'rowid')
                ->lazy() as $row) {
                $values = collect($columns)
                    ->map(fn (string $column): string => $this->quoteValue($pdo, $row->{$column} ?? null))
                    ->join(', ');

                $statements[] = 'INSERT INTO '.$this->quoteIdentifier($tableName).' ('.collect($columns)->map(fn (string $column): string => $this->quoteIdentifier($column))->join(', ').') VALUES ('.$values.');';
            }
        }

        $statements[] = 'COMMIT;';
        $statements[] = 'PRAGMA foreign_keys=ON;';

        return implode(PHP_EOL, $statements).PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function externalDump(string $driver, array $connection): string
    {
        $command = match ($driver) {
            'mysql', 'mariadb' => [
                'mysqldump',
                '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
                '--port='.(string) ($connection['port'] ?? '3306'),
                '--user='.(string) ($connection['username'] ?? ''),
                '--single-transaction',
                '--skip-comments',
                (string) ($connection['database'] ?? ''),
            ],
            'pgsql' => [
                'pg_dump',
                '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
                '--port='.(string) ($connection['port'] ?? '5432'),
                '--username='.(string) ($connection['username'] ?? ''),
                '--dbname='.(string) ($connection['database'] ?? ''),
                '--no-owner',
                '--no-privileges',
            ],
            default => throw new \RuntimeException("Driver database [{$driver}] belum didukung untuk backup otomatis."),
        };

        $environment = match ($driver) {
            'mysql', 'mariadb' => ['MYSQL_PWD' => (string) ($connection['password'] ?? '')],
            'pgsql' => ['PGPASSWORD' => (string) ($connection['password'] ?? '')],
            default => [],
        };
        $process = new Process($command, base_path(), $environment, null, 120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Backup database gagal dijalankan.');
        }

        return $process->getOutput();
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return (string) $pdo->quote((string) $value);
    }
}
