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

    if (empty($argv[1])
        || (!empty($rangeAttribute)
            && empty($argv[2]))
    ) {
        throw new Exception('You must provide a complete Primary Key');
    }

    $key = [
        $hashAttribute => [
            ATTRIBUTE_TYPE_STRING => $argv[1],
        ],
    ];

    if (!empty($rangeAttribute)) {
        $key[$rangeAttribute] = [ ATTRIBUTE_TYPE_STRING => $argv[2] ];
    }

    $getResult = $dynClient->getItem([
        'TableName' => $tableName,
        'Key' => $key,
    ]);

    $attributes = [];
    $hashValue = $rangeValue = '';

    foreach ($getResult['Item'] as $attributeName => $attributeData) {
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

    echo "Item [ $hashAttribute => $hashValue"
        . (!empty($rangeValue) ? ", $rangeAttribute => $rangeValue" : '');

    foreach ($attributes as $name => $value) {
        echo ", $name => $value";
    }

    echo " ]\n";
} catch (\Aws\DynamoDB\Exception\DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
