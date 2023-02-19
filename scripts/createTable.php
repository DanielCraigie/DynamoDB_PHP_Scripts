<?php

use Aws\DynamoDb\DynamoDbClient;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Creates an empty Table

Usage:
    php createTable.php [options]

Options:
    -h --help   This help

<?php
    exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */
/** @var string $partitionKeyName */
/** @var string $sortKeyName */
/** @var int $readCapacityUnits */
/** @var int $writeCapacityUnits */

$tableSpecification = [
    'AttributeDefinitions' => [
        [
            'AttributeName' => $partitionKeyName,
            'AttributeType' => ATTRIBUTE_TYPE_STRING,
        ],
        [
            'AttributeName' => $sortKeyName,
            'AttributeType' => ATTRIBUTE_TYPE_STRING,
        ],
    ],
    'KeySchema' => [
        [
            'AttributeName' => $partitionKeyName,
            'KeyType' => KEY_TYPE_HASH,
        ],
        [
            'AttributeName' => $sortKeyName,
            'KeyType' => KEY_TYPE_RANGE,
        ],
    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits' => $readCapacityUnits,
        'WriteCapacityUnits' => $writeCapacityUnits,
    ],
    'TableName' => $tableName,
];

try {
    $createResult = $dynClient->createTable($tableSpecification);

    if ($createResult['TableDescription']['TableStatus'] != 'ACTIVE') {
        throw new Exception('Table: ' . $tableName . ' could not be created.');
    }

    echo "Table[$tableName] created.\n";
} catch (\Aws\DynamoDB\Exception\DynamoDbException $dbException) {
    if (preg_match('/Cannot create preexisting table/', $dbException->getMessage())) {
        echo "Table[$tableName] already exists.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
