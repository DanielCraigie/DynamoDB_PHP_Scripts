<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    if (!empty($argv[1])) {
        $query = json_decode($argv[1], true);
    } else {
        throw new Exception('You must provide a hash query string');
    }

    $tableDescription = $dynClient->describeTable([ 'TableName' => $tableName ]);

    $hashAttribute = $rangeAttribute = null;
    foreach ($tableDescription['Table']['KeySchema'] as $key) {
        switch ($key['KeyType']) {
            case KEY_TYPE_HASH:
                $hashAttribute = $key['AttributeName'];
                break;
            case KEY_TYPE_RANGE:
                $rangeAttribute = $key['AttributeName'];
        }
    }

    $keyConditionExpression = '#hash = :hash';
    $expressionAttributeNames = [ '#hash' => $hashAttribute ];
    $expressionAttributeValues = [
        ':hash' => [
            ATTRIBUTE_TYPE_STRING => $query['hash'],
        ],
    ];

    if (!empty($query['range'])) {
        $keyConditionExpression .= ' AND #range = :range';
        $expressionAttributeNames['#range'] = $rangeAttribute;
        $expressionAttributeValues[':range'] = [ ATTRIBUTE_TYPE_STRING => $query['range'] ];
    }

    $queryResults = $dynClient->query([
        'TableName' => $tableName,
        'KeyConditionExpression' => $keyConditionExpression,
        'ExpressionAttributeNames' => $expressionAttributeNames,
        'ExpressionAttributeValues' => $expressionAttributeValues,
    ]);

    echo "Query found {$queryResults['ScannedCount']} of {$queryResults['Count']} Items\n";

    $count = 1;
    foreach ($queryResults['Items'] as $item) {
        $attributes = [];
        $hashValue = $rangeValue = '';

        foreach ($item as $attributeName => $attributeData) {
            // we are only expecting a single value to be stored in the Attribute
            $attributeValue = reset($attributeData);

            switch ($attributeName) {
                case $hashAttribute:
                    $hashValue = $attributeValue;
                    break;
                case $rangeAttribute:
                    $rangeValue = $attributeValue;
                    break;
                default:
                    $attributes[$attributeName] = $attributeValue;
            }
        }

        echo "Item $count [ $hashAttribute => $hashValue"
            . (!empty($rangeValue) ? ", $rangeAttribute => $rangeValue" : '');

        foreach ($attributes as $name => $value) {
            echo ", $name => $value";
        }

        echo " ]\n";

        $count++;
    }

    echo "End Query\n";
} catch (\Aws\DynamoDB\Exception\DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
