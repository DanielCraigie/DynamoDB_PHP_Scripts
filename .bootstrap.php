<?php

// min PHP version requirement
$minVersion = '7.4.0';
if (version_compare(PHP_VERSION, $minVersion) < 0)
    throw new Exception('Requires PHP ' . $minVersion . ' or greater.');

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sdk = new \Aws\Sdk([
    'region' => $_ENV['REGION'],
    'version' => 'latest',
]);

$dynClient = $sdk->createDynamoDb([
    'endpoint' => $_ENV['ENDPOINT']
]);

define('KEY_TYPE_HASH', 'HASH');
define('KEY_TYPE_RANGE', 'RANGE');

define('ATTRIBUTE_TYPE_BINARY', 'B');
define('ATTRIBUTE_TYPE_BOOL', 'BOOL');
define('ATTRIBUTE_TYPE_BINARY_SET', 'BS');
define('ATTRIBUTE_TYPE_LIST', 'L');
define('ATTRIBUTE_TYPE_MAP', 'M');
define('ATTRIBUTE_TYPE_NUMBER', 'N');
define('ATTRIBUTE_TYPE_NUMBER_SET', 'NS');
define('ATTRIBUTE_TYPE_NULL', 'NULL');
define('ATTRIBUTE_TYPE_STRING', 'S');
define('ATTRIBUTE_TYPE_STRING_SET', 'SS');

$tableName = 'People';
