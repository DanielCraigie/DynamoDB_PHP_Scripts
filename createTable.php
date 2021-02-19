<?php

require_once '.bootstrap.php';

$tableName = 'People';

try {
    /** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
    $deleteResult = $dynClient->deleteTable([
        'TableName' => $tableName,
    ]);

    $createResult = $dynClient->createTable([
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'Name',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'Information',
                'AttributeType' => 'S',
            ],
        ],
        'KeySchema' => [
            [
                'AttributeName' => 'Name',
                'KeyType' => 'HASH',
            ],
            [
                'AttributeName' => 'Information',
                'KeyType' => 'RANGE',
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
} catch (Exception $e) {
    echo get_class($e) . ' ' . $e->getMessage() . "\n";
}
