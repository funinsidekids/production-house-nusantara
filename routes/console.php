<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:hosting-check', function () {
    $failures = [];
    $phpVersion = PHP_VERSION;
    $requiredPhp = '8.4.0';
    $requiredExtensions = [
        'ctype',
        'curl',
        'dom',
        'fileinfo',
        'filter',
        'hash',
        'iconv',
        'intl',
        'json',
        'libxml',
        'mbstring',
        'openssl',
        'pcre',
        'pdo',
        'phar',
        'session',
        'simplexml',
        'tokenizer',
        'xml',
        'xmlreader',
        'xmlwriter',
        'zip',
    ];

    $this->newLine();
    $this->line('Hostinger Compatibility Check');
    $this->line(str_repeat('-', 32));

    if (version_compare($phpVersion, $requiredPhp, '>=')) {
        $this->info("PHP version: {$phpVersion} (OK)");
    } else {
        $this->error("PHP version: {$phpVersion} (Minimal {$requiredPhp})");
        $failures[] = 'php_version';
    }

    foreach ($requiredExtensions as $extension) {
        if (extension_loaded($extension)) {
            $this->line("ext-{$extension}: OK");
        } else {
            $this->error("ext-{$extension}: MISSING");
            $failures[] = "ext_{$extension}";
        }
    }

    $writablePaths = [
        storage_path(),
        base_path('bootstrap/cache'),
    ];

    foreach ($writablePaths as $path) {
        if (is_writable($path)) {
            $this->line("writable {$path}: OK");
        } else {
            $this->error("writable {$path}: FAIL");
            $failures[] = "writable_{$path}";
        }
    }

    if (config('app.key')) {
        $this->line('APP_KEY: OK');
    } else {
        $this->error('APP_KEY: MISSING');
        $failures[] = 'app_key';
    }

    $appUrl = (string) config('app.url');
    if ($appUrl !== '') {
        $this->line("APP_URL: {$appUrl}");
    } else {
        $this->error('APP_URL: EMPTY');
        $failures[] = 'app_url';
    }

    $dbConnection = (string) config('database.default');
    if ($dbConnection !== '') {
        $this->line("DB connection: {$dbConnection}");
    } else {
        $this->error('DB connection: EMPTY');
        $failures[] = 'db_connection';
    }

    $queue = (string) config('queue.default');
    $broadcast = (string) config('broadcasting.default');
    $this->line("QUEUE_CONNECTION: {$queue}");
    $this->line("BROADCAST_CONNECTION: {$broadcast}");

    $this->newLine();
    if (count($failures) === 0) {
        $this->info('Hosting check passed.');

        return self::SUCCESS;
    }

    $this->error('Hosting check failed. Fix the reported items before go-live.');

    return self::FAILURE;
})->purpose('Validate PHP/extensions and runtime readiness for shared hosting deployment');
