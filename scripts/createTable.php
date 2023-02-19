<?php

use Aws\DynamoDb\DynamoDbClient;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Create database table

Usage:
    php createTable.php [options]
    php createTable.php -t <name>

Options:
    -h --help   This help
    -t --table  Table name

<?php
    exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

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
 * functions to interact with user
 */
$getTableKeyFromUser = function(string $keyName, string $errorMessage, string $default = ''): string {
    while (true) {
        echo "Please select a $keyName Key name" . (!empty($default) ? " [$default]" : '') . ': ';
        $key = rtrim(fgets(STDIN));
        if (!empty($default)
            && empty($key)
        ) {
            return $default;
        } elseif (preg_match('/^[A-Za-z0-9_\-\.]+$/', $key)
            && mb_strlen($key) <= 255
        ) {
            return $key;
        } else {
            echo "Error: $errorMessage, please try again\n";
        }
    }
};

$doesUserWantSortKey = function(): bool {
    while (true) {
        echo 'Do you want to specify a Sort Key? (Y/n) ';
        $answer = rtrim(fgets(STDIN));
        if (empty($answer)
            || $answer === 'y'
        ) {
            return true;
        } elseif ($answer === 'n') {
            return false;
        } else {
            echo "Error: Invalid input, value must be 'y' or 'n'\n";
        }
    }
};

/*
 * confirm table structure with user
 */
getTableNameFromUser($tableName, $nameOverride);
$partitionKey = $getTableKeyFromUser('Partition', 'You must enter a valid key name', 'PK');
if ($doesUserWantSortKey()) {
    $sortKey = $getTableKeyFromUser('Sort', 'You must enter a valid key name', 'SK');
}

$tableSpecification = [
    'AttributeDefinitions' => [
        [
            'AttributeName' => $partitionKey,
            'AttributeType' => ATTRIBUTE_TYPE_STRING,
        ],
    ],
    'KeySchema' => [
        [
            'AttributeName' => $partitionKey,
            'KeyType' => KEY_TYPE_HASH,
        ],
    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits' => 5,
        'WriteCapacityUnits' => 5,
    ],
    'TableName' => $tableName,
];

if (!empty($sortKey)) {
    $tableSpecification['AttributeDefinitions'][] = [
        'AttributeName' => $sortKey,
        'AttributeType' => ATTRIBUTE_TYPE_STRING,
    ];
    $tableSpecification['KeySchema'][] = [
        'AttributeName' => $sortKey,
        'KeyType' => KEY_TYPE_RANGE,
    ];
}

try {
    $createResult = $dynClient->createTable($tableSpecification);

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
