<?php

use Dotenv\Dotenv;
use Aws\Sdk;

// min PHP version requirement
$minVersion = '7.4.0';
if (version_compare(PHP_VERSION, $minVersion) < 0)
    throw new Exception('Requires PHP ' . $minVersion . ' or greater.');

require 'vendor/autoload.php';

/*
 * Init Dotenv for $_ENV variables
 */
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

/*
 * Init AWS PHP SDK
 */
$sdk = new Sdk([
    'region' => $_ENV['REGION'],
    'version' => 'latest',
]);

/*
 * Create DynamoDB API connection
 */
$dynClient = $sdk->createDynamoDb([
    'endpoint' => $_ENV['ENDPOINT']
]);

/*
 * Define DynamoDB constants for Scripts
 */
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

/**
 * Returns HASH & optional RANGE Table Item Attributes
 * @return array
 */
function getPrimaryKeyAttributes(): array
{
    global $dynClient;
    global $tableName;

    $tableDescription = $dynClient->describeTable([ 'TableName' => $tableName ]);

    $hashAttribute = $rangeAttribute = null;
    foreach ($tableDescription['Table']['KeySchema'] as $key) {
        switch ($key['KeyType']) {
            case KEY_TYPE_HASH:
                $hashAttribute = $key['AttributeName'];
                break;
            case KEY_TYPE_RANGE:
                $rangeAttribute = $key['AttributeName'];
        }
    }

    return [$hashAttribute, $rangeAttribute];
}
