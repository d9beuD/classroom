<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Fresh SQLite test database with fixtures for each run. The migrations are MySQL-only, so the schema comes from the mapping.
// shortcut: tests share this data and see each other's writes, add dama/doctrine-test-bundle once that causes flaky tests.
// typescript:build compiles assets/*.ts, which importmap('app') in base.html.twig needs on a fresh checkout.
foreach (['doctrine:schema:drop --force --full-database', 'doctrine:schema:create', 'doctrine:fixtures:load --no-interaction', 'typescript:build'] as $command) {
    passthru(sprintf('%s %s/bin/console %s --env=test --quiet', escapeshellarg(PHP_BINARY), escapeshellarg(dirname(__DIR__)), $command), $exitCode);
    if (0 !== $exitCode) {
        throw new RuntimeException(sprintf('Test database setup failed: "%s".', $command));
    }
}
