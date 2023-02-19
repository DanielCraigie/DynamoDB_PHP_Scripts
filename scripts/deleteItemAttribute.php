<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Removes an Attribute for a specific Item referenced by a Partition and Sort Key pair

Usage:
    php deleteItemAttribute.php [options]
    php deleteItemAttribute.php -p <PK> -s <SK> -a <attribute>

Options:
    -h --help            This help
    -p --partition-key   Partition Key
    -s --sort-key        Sort key
    -a --attribute-name  Attribute name to be modified

<?php
exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */
/** @var string $partitionKeyName */
/** @var string $sortKeyName */

$partitionKey = $sortKey = $attributeName = null;

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
        case '-a':
        case '--attribute':
            if (empty($argv[$index + 1])) {
                echo "Error: you must provide an Attribute Name\n";
                exit(1);
            }
            $attributeName = $argv[$index + 1];
            break;
    }
}

foreach ([
    'partitionKey' => 'a Partition Key',
    'sortKey' => 'a Sort Key',
    'attributeName' => 'an Attribute Name',
] as $variable => $variableName) {
    if (empty(${$variable})) {
        echo "Error: you must provide $variableName\n";
        exit(1);
    }
}

try {
    echo "Removing attribute: $attributeName from key: $partitionKey, $sortKey\n";

    $updateResult = $dynClient->updateItem([
        'TableName' => $tableName,
        'Key' => [
            $partitionKeyName => [
                ATTRIBUTE_TYPE_STRING => $partitionKey,
            ],
            $sortKeyName => [
                ATTRIBUTE_TYPE_STRING => $sortKey,
            ],
        ],
        'UpdateExpression' => "REMOVE $attributeName",
    ]);

    echo "End Attribute Delete\n";
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
