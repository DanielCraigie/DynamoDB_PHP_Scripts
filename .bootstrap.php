<?php

// min PHP version requirement
$minVersion = '7.4.0';
if (version_compare(PHP_VERSION, $minVersion) < 0)
    throw new Exception('Requires PHP ' . $minVersion . ' or greater.');

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
