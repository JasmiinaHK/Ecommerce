<?php

echo "Starting database setup...\n";

// Load Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Run the console kernel
$kernel = $app->make(Illwarehousection::class);

// Run migrations
echo "\nRunning migrations...\n";
Artisan::call('migrate:fresh', [
    '--force' => true,
]);

echo "\nRunning seeders...\n";
Artisan::call('db:seed', [
    '--force' => true,
]);

echo "\nDatabase setup completed successfully!\n";
