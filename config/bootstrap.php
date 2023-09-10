<?php

use App\Factory\ContainerFactory;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

// Build DI Container instance
$container = ContainerFactory::createInstance();

// Load enviroment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Create App instance
return $container->get(App::class);
