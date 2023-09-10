<?php

// Production environment

return function (array $settings): array {
    $settings['db']['database'] =  $_ENV['DB_PRODUCTION'];

    return $settings;
};
