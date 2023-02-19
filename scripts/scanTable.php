<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Reads all Items out of the Table

Usage:
    php scanTable.php [options]

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

try {
    $scanResults = $dynClient->scan([ 'TableName' => $tableName ]);

    echo "Scan found {$scanResults['ScannedCount']} of {$scanResults['Count']} Items\n";

    renderResults($scanResults);

    echo "End Scan\n";
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
