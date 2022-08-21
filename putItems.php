<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDB\Exception\DynamoDbException;
use Faker\Factory;

require_once '.bootstrap.php';

/** @var DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    for ($i = 1; $i <= 10; $i++) {
        $faker = Factory::create();

        $uuid = $faker->uuid;
        $firstName = $faker->firstName;
        $lastName = $faker->lastName;

        echo "Adding[$i] $firstName $lastName... ";

        $putPersonal = $dynClient->putItem([
            'TableName' => $tableName,
            'Item' => [
                'PK' => [ ATTRIBUTE_TYPE_STRING => $uuid ],
                'SK' => [ ATTRIBUTE_TYPE_STRING => 'Personal' ],
                'Title' => [ ATTRIBUTE_TYPE_STRING => $faker->title ],
                'Forename' => [ ATTRIBUTE_TYPE_STRING => $firstName ],
                'Surname' => [ ATTRIBUTE_TYPE_STRING => $lastName ],
            ],
        ]);

        $putAddress = $dynClient->putItem([
            'TableName' => $tableName,
            'Item' => [
                'PK' => [ ATTRIBUTE_TYPE_STRING => $uuid ],
                'SK' => [ ATTRIBUTE_TYPE_STRING => 'Address' ],
                'Building' => [ ATTRIBUTE_TYPE_STRING => $faker->buildingNumber ],
                'Street1' => [ ATTRIBUTE_TYPE_STRING => $faker->streetName ],
                'City' => [ ATTRIBUTE_TYPE_STRING => $faker->city ],
                'State' => [ ATTRIBUTE_TYPE_STRING => $faker->state ],
                'Postcode' => [ ATTRIBUTE_TYPE_STRING => $faker->postcode ],
                'Country' => [ ATTRIBUTE_TYPE_STRING => $faker->country ],
            ],
        ]);

        $putContact = $dynClient->putItem([
            'TableName' => $tableName,
            'Item' => [
                'PK' => [ ATTRIBUTE_TYPE_STRING => $uuid ],
                'SK' => [ ATTRIBUTE_TYPE_STRING => 'Contact' ],
                'Phone' => [ ATTRIBUTE_TYPE_STRING => $faker->phoneNumber ],
                'Email' => [ ATTRIBUTE_TYPE_STRING => $faker->email ],
            ],
        ]);

        echo "Done\n";
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
