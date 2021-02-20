<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
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
    echo get_class($e) . ' ' . $e->getMessage() . "\n";
}
