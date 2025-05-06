<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$projectRoot = __DIR__.'/..';

$commands = [
    'doctrine:database:drop --force --if-exists',
    'doctrine:database:create',
    'doctrine:schema:update --force',
    'doctrine:fixtures:load',
];

foreach ($commands as $command) {
    passthru("{$projectRoot}/bin/console {$command} --env=test --no-interaction");
}
