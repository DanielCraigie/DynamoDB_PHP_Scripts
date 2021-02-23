<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;

require_once '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    if (!empty($argv[1])) {
        $update = json_decode($argv[1], true);
    } else {
        throw new Exception('You must provide a hash query string');
    }

    if (empty($update['key'])) {
        throw new Exception('You must provide a "key" element in the JSON array');
    }

    if (empty($update['actions'])
        || (empty($update['actions']['set'])
            && empty($update['actions']['remove'])
            && empty($update['actions']['add'])
            && empty($update['actions']['delete'])
        )
    ) {
        throw new Exception('You must provide a valid "actions" element in the JSON array');
    }

    list ($hashAttribute, $rangeAttribute) = getPrimaryKeyAttributes();

    $key = [
        $hashAttribute => [
            ATTRIBUTE_TYPE_STRING => $update['key']['hash'],
        ],
    ];

    if (!empty($rangeAttribute)) {
        $key[$rangeAttribute] = [ ATTRIBUTE_TYPE_STRING => $update['key']['range'] ];
    }

    $updateExpression = '';
    $updateExpressionValues = [];
    foreach ($update['actions'] as $action => $attributes) {
        switch ($action) {
            case 'set':
                $updateExpression .= (empty($updateExpression) ? '' : ', ') . 'SET';
                foreach ($attributes as $attribute => $value) {
                    $updateExpression .= " $attribute=:$attribute, ";
                    $updateExpressionValues[":$attribute"] = [ ATTRIBUTE_TYPE_STRING => $value];
                }
                $updateExpression = rtrim($updateExpression, ', ');
                break;
            case 'remove':
                $updateExpression .= (empty($updateExpression) ? '' : ', ') . 'REMOVE ';
                if (is_array($attributes)) {
                    $updateExpression .= implode(',', $attributes);
                } else {
                    $updateExpression .= $attributes;
                }
                break;
            case 'add':
            case 'delete':
                throw new Exception(__FILE__ . " does not support the \"$action\" action at this time");
        }
    }

    echo "Updating key: $hashAttribute={$update['key']['hash']}" . (!empty($rangeAttribute) ? ", $rangeAttribute={$update['key']['range']}\n" : "\n");

    $expression = [
        'TableName' => $tableName,
        'Key' => $key,
        'UpdateExpression' => $updateExpression,
    ];

    if (!empty($updateExpressionValues)) {
        $expression['ExpressionAttributeValues'] = $updateExpressionValues;
    }

    $updateResult = $dynClient->updateItem($expression);

    echo "End Update\n";
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
