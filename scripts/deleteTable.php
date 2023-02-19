<?php

use Aws\DynamoDb\DynamoDbClient;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Deletes the Table

Usage:
    php deleteTable.php [options]

Options:
    -h --help   This help

<?php
    exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    $deleteResult = $dynClient->deleteTable([
        'TableName' => $tableName,
    ]);

    echo "Table[$tableName] deleted.\n";
} catch (\Aws\DynamoDB\Exception\DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
