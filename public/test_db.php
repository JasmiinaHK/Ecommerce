<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

$db = [
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'laravel',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
];

$capsule = new DB;
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $pdo = $capsule->getConnection()->getPdo();
    echo "Connected to the database successfully!\n";
    
    // Test query
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . (empty($tables) ? 'No tables found' : implode(', ', $tables)) . "\n";
    
} catch (\Exception $e) {
    die("Could not connect to the database. Error: " . $e->getMessage() . "\n");
}
