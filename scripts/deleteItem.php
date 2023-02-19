<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Removes an Item referenced by a Partition and Sort Key pair

Usage:
    php deleteItem.php [options]
    php deleteItem.php -p <PK> -s <SK>

Options:
    -h --help            This help
    -p --partition-key   Partition Key
    -s --sort-key        Sort key

<?php
exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */
/** @var string $partitionKeyName */
/** @var string $sortKeyName */

$partitionKey = $sortKey = null;

/*
 * parse CLI arguments
 */
foreach ($argv as $index => $arg) {
    switch ($arg) {
        case '-p':
        case '--partition-key':
            if (empty($argv[$index + 1])) {
                echo "Error: you must provide a Partition Key\n";
                exit(1);
            }
            $partitionKey = $argv[$index + 1];
            break;
        case '-s':
        case '--sort-key':
            if (empty($argv[$index + 1])) {
                echo "Error: you must provide a Sort Key\n";
                exit(1);
            }
            $sortKey = $argv[$index + 1];
            break;
    }
}

foreach ([
    'partitionKey' => 'a Partition Key',
    'sortKey' => 'a Sort Key',
] as $variable => $variableName) {
    if (empty(${$variable})) {
        echo "Error: you must provide $variableName\n";
        exit(1);
    }
}

try {
    echo "Removing key: $partitionKey, $sortKey\n";

    $updateResult = $dynClient->deleteItem([
        'TableName' => $tableName,
        'Key' => [
            $partitionKeyName => [
                ATTRIBUTE_TYPE_STRING => $partitionKey,
            ],
            $sortKeyName => [
                ATTRIBUTE_TYPE_STRING => $sortKey,
            ],
        ],
    ]);

    echo "End Item Delete\n";
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
