<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Pasha234\Hw10\SearchBooksCommand;
use Symfony\Component\Console\Application;

if (file_exists(__DIR__ . '.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$command = new SearchBooksCommand();

$app = new Application();
$app->add($command);
$app->setDefaultCommand($command->getName());

$app->run();
