<?php

use Dotenv\Dotenv;
use Aws\Sdk;

// min PHP version requirement
$minVersion = '7.4.0';
if (version_compare(PHP_VERSION, $minVersion) < 0) {
    throw new Exception('Requires PHP ' . $minVersion . ' or greater.');
}

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
    'credentials' => [
        'key' => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
    'region' => $_ENV['REGION'],
    'version' => 'latest',
]);

/*
 * Create DynamoDB API connection
 */
$dynClient = $sdk->createDynamoDb([
    'endpoint' => $_ENV['ENDPOINT'],
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

/*
 * Static table configuration
 */
$tableName = 'People';
$partitionKeyName = 'PK';
$sortKeyName = 'SK';
$readCapacityUnits = 5;
$writeCapacityUnits = 5;

function renderResults(Aws\Result $results) {
    global $partitionKeyName, $sortKeyName;

    $count = 1;
    foreach ($results['Items'] as $item) {
        $attributes = [];
        $hashValue = $rangeValue = '';

        /*
         * DynamoDb treats the Partition Key and (optional) Sort Key as standard Attributes of the Item
         * Therefore, to present the data in a useful manner (by PK and SK) we need to filter those values out first
         */
        foreach ($item as $attributeName => $attributeData) {
            /*
             * the SDK will return each Attribute as an array [ DataType => Value ]
             */
            $attributeValue = reset($attributeData);

            switch ($attributeName) {
                case $partitionKeyName:
                    $hashValue = $attributeValue;
                    break;
                case $sortKeyName:
                    $rangeValue = $attributeValue;
                    break;
                default:
                    $attributes[$attributeName] = $attributeValue;
            }
        }

        echo "Item $count [ $partitionKeyName => $hashValue, $sortKeyName => $rangeValue";

        foreach ($attributes as $name => $value) {
            echo ", $name => $value";
        }

        echo " ]\n";

        $count++;
    }
}
