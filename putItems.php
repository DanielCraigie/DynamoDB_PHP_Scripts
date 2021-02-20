<?php

require_once '.bootstrap.php';

/** @var \Aws\DynamoDb\DynamoDbClient $dynClient */
/** @var string $tableName */

try {
    for ($i = 1; $i <= 10; $i++) {
        $faker = Faker\Factory::create();

        echo "Adding[$i] {$faker->name}... ";

        $putPersonal = $dynClient->putItem([
            'TableName' => $tableName,
            'Item' => [
                'Name' => [ ATTRIBUTE_TYPE_STRING => $faker->firstName . ' ' . $faker->lastName ],
                'Information' => [ ATTRIBUTE_TYPE_STRING => 'Personal' ],
                'Title' => [ ATTRIBUTE_TYPE_STRING => $faker->title ],
                'Forename' => [ ATTRIBUTE_TYPE_STRING => $faker->firstName ],
                'Surname' => [ ATTRIBUTE_TYPE_STRING => $faker->lastName ],
            ],
        ]);

        $putAddress = $dynClient->putItem([
            'TableName' => $tableName,
            'Item' => [
                'Name' => [ ATTRIBUTE_TYPE_STRING => $faker->firstName . ' ' . $faker->lastName ],
                'Information' => [ ATTRIBUTE_TYPE_STRING => 'Address' ],
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
                'Name' => [ ATTRIBUTE_TYPE_STRING => $faker->firstName . ' ' . $faker->lastName ],
                'Information' => [ ATTRIBUTE_TYPE_STRING => 'Contact' ],
                'Phone' => [ ATTRIBUTE_TYPE_STRING => $faker->phoneNumber ],
                'Email' => [ ATTRIBUTE_TYPE_STRING => $faker->email ],
            ],
        ]);

        echo "Done\n";
    }
} catch (Exception $e) {
    echo get_class($e) . ' ' . $e->getMessage() . "\n";
}
