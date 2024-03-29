<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;
use Faker\Factory;

/*
 * Help block
 */
if (in_array('-h', $argv)
    || in_array('--help', $argv)
) {?>
Write Items into the Table

Usage:
    php putItems.php [options]
    php putItems.php -m <number>

Options:
    -h --help   This help
    -m --mock   Number of mocked items to add to table (positive integer)

<?php
exit(0);
}

require_once '..' . DIRECTORY_SEPARATOR . '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */
/** @var string $mockCount */
/** @var string $partitionKeyName */
/** @var string $sortKeyName */

/*
 * parse CLI arguments
 */
$mockItems = false;
foreach ($argv as $index => $arg) {
    switch ($arg) {
        case '-m':
        case '--mock':
            if (!is_int($argv[$index + 1])
                || $argv[$index + 1] < 1
            ) {
                echo "Error: you must enter a positive integer when specifying number of mocked items\n";
                exit(1);
            }
            $mockCount = $argv[$index + 1];
            $mockItems = true;
            break;
    }
}

/*
 * functions to interact with user
 */
$getMockCountFromUser = function(): string {
    while (true) {
        echo 'How many mocked items would you like to add? [100]: ';
        $count = (int) rtrim(fgets(STDIN));
        if (empty($count)) {
            return 100;
        } elseif (is_int($count)
            && $count > 0
            && $count < 1_000_000
        ) {
            return $count;
        } else {
            echo "Error: value must be a positive integer < 1 million, please try again\n";
        }
    }
};

/*
 * confirm table structure with user
 */
if (!$mockItems) {
    $mockCount = $getMockCountFromUser();
    $mockItems = $mockCount > 0;
}

try {
    if ($mockItems) {
        for ($i = 1; $i <= $mockCount; $i++) {
            $faker = Factory::create();

            $uuid = $faker->uuid;
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;

            echo "Adding[$i] $firstName $lastName... ";

            $putPersonal = $dynClient->putItem([
                'TableName' => $tableName,
                'Item' => [
                    $partitionKeyName => [ATTRIBUTE_TYPE_STRING => $uuid],
                    $sortKeyName => [ATTRIBUTE_TYPE_STRING => 'Personal'],
                    'Title' => [ATTRIBUTE_TYPE_STRING => $faker->title],
                    'Forename' => [ATTRIBUTE_TYPE_STRING => $firstName],
                    'Surname' => [ATTRIBUTE_TYPE_STRING => $lastName],
                ],
            ]);

            $putAddress = $dynClient->putItem([
                'TableName' => $tableName,
                'Item' => [
                    $partitionKeyName => [ATTRIBUTE_TYPE_STRING => $uuid],
                    $sortKeyName => [ATTRIBUTE_TYPE_STRING => 'Address'],
                    'Building' => [ATTRIBUTE_TYPE_STRING => $faker->buildingNumber],
                    'Street1' => [ATTRIBUTE_TYPE_STRING => $faker->streetName],
                    'City' => [ATTRIBUTE_TYPE_STRING => $faker->city],
                    'State' => [ATTRIBUTE_TYPE_STRING => $faker->state],
                    'Postcode' => [ATTRIBUTE_TYPE_STRING => $faker->postcode],
                    'Country' => [ATTRIBUTE_TYPE_STRING => $faker->country],
                ],
            ]);

            $putContact = $dynClient->putItem([
                'TableName' => $tableName,
                'Item' => [
                    $partitionKeyName => [ATTRIBUTE_TYPE_STRING => $uuid],
                    $sortKeyName => [ATTRIBUTE_TYPE_STRING => 'Contact'],
                    'Phone' => [ATTRIBUTE_TYPE_STRING => $faker->phoneNumber],
                    'Email' => [ATTRIBUTE_TYPE_STRING => $faker->email],
                ],
            ]);

            echo "Done\n";
        }
    }
} catch (DynamoDbException $dbException) {
    if (preg_match('/Cannot do operations on a non-existent table/', $dbException->getMessage())) {
        echo "\nError: Table[$tableName] not found.\n";
    } else {
        throw $dbException;
    }
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
