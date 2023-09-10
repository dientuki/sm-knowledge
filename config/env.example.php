<?php

// Secret credentials

return function (array $settings): array {

    $settings['db']['host'] = $_ENV['DB_HOST'];
    $settings['db']['port'] = $_ENV['DB_PORT'];
    $settings['db']['username'] = $_ENV['DB_USERNAME'];
    $settings['db']['password'] = $_ENV['DB_PASSWORD'];

    if (defined('PHPUNIT_COMPOSER_INSTALL')) {
        // PHPUnit test database
        $settings['db']['database'] = $_ENV['DB_TEST'];
    } else {
        // Local dev database
        $settings['db']['database'] = $_ENV['DB_DEVELOPMENT'];
    }

    return $settings;
};
