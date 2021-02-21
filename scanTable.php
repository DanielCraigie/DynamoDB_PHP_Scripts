<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
/** @var string $tableName */

try {
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

    $scanResults = $dynClient->scan([ 'TableName' => $tableName ]);

    echo "Scan found {$scanResults['ScannedCount']} of {$scanResults['Count']} Items\n";

    $count = 1;
    foreach ($scanResults['Items'] as $item) {
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

    echo "End Scan\n";
} catch (\Aws\DynamoDB\Exception\DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
