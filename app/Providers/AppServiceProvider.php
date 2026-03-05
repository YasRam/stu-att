<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureMySqlDatabaseExists();
    }

    /**
     * Create MySQL database if it does not exist (so migrate works without manual CREATE DATABASE).
     */
    private function ensureMySqlDatabaseExists(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        $database = config('database.connections.mysql.database');
        if (empty($database)) {
            return;
        }

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $database) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // Database might already exist or MySQL not running – let migrate fail with a clear error
        }
    }
}
