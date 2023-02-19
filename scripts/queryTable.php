<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Returns all Items for a specific Partition Key (and optional Sort Key)

Usage:
    php queryTable.php [options]
    php queryTable.php -p <PK>
    php queryTable.php -p <PK> -s <SK>

Options:
    -h --help          This help
    -p --partition-key Partition Key to return
    -s --sort-key      Optional sort key to filter results by

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
    $keyConditionExpression = '#hash = :hash';
    $expressionAttributeNames = [ '#hash' => $partitionKeyName ];
    $expressionAttributeValues = [
        ':hash' => [
            ATTRIBUTE_TYPE_STRING => $partitionKey,
        ],
    ];

    if (!empty($sortKey)) {
        $keyConditionExpression .= ' AND #range = :range';
        $expressionAttributeNames['#range'] = $sortKeyName;
        $expressionAttributeValues[':range'] = [ ATTRIBUTE_TYPE_STRING => $sortKey ];
    }

    $queryResults = $dynClient->query([
        'TableName' => $tableName,
        'KeyConditionExpression' => $keyConditionExpression,
        'ExpressionAttributeNames' => $expressionAttributeNames,
        'ExpressionAttributeValues' => $expressionAttributeValues,
    ]);

    echo "Query found {$queryResults['ScannedCount']} of {$queryResults['Count']} Items\n";

    renderResults($queryResults);

    echo "End Query\n";
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
