<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    $results = $dynClient->scan([
        'TableName' => $tableName,
    ]);

    echo "Scan found {$results['ScannedCount']} Items\n";

    foreach ($results['Items'] as $item) {
        print_r($item);
    }

    echo "End Scan\n";
} catch (Exception $e) {
    echo get_class($e) . ' ' . $e->getMessage() . "\n";
}
