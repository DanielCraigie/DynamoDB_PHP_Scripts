<?php

use Aws\DynamoDb\DynamoDbClient;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Delete database table

Usage:
    php deleteTable.php [options]
    php deleteTable.php -t <name>

Options:
    -h --help   This help
    -t --table  Table name

<?php
    exit(0);
}

require_once '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */

/*
 * parse CLI arguments
 */
$nameOverride = false;
foreach ($argv as $index => $arg) {
    switch ($arg) {
        case '-t':
        case '--table':
            if (empty($argv[$index + 1])
                || !validateTableName($argv[$index + 1])
            ) {
                echo "Error: A valid table name must be supplied\n";
                exit(1);
            }
            $tableName = $argv[$index + 1];
            $nameOverride = true;
            break;
    }
}

/*
 * ask user to confirm table name (if -t|--table hasn't been used)
 */
getTableNameFromUser($tableName, $nameOverride);

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
