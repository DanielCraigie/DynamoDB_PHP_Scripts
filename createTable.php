<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    $createResult = $dynClient->createTable([
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'Name',
                'AttributeType' => ATTRIBUTE_TYPE_STRING,
            ],
            [
                'AttributeName' => 'Information',
                'AttributeType' => ATTRIBUTE_TYPE_STRING,
            ],
        ],
        'KeySchema' => [
            [
                'AttributeName' => 'Name',
                'KeyType' => KEY_TYPE_HASH,
            ],
            [
                'AttributeName' => 'Information',
                'KeyType' => KEY_TYPE_RANGE,
            ],
        ],
        'ProvisionedThroughput' => [
            'ReadCapacityUnits' => 5,
            'WriteCapacityUnits' => 5,
        ],
        'TableName' => $tableName,
    ]);

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
