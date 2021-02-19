<?php

namespace App;

use Aws\DynamoDb\DynamoDbClient;

class ClientFactory
{
    /**
     * @return array
     */
    private static function config()
    {
        return ['region' => $_ENV['REGION']];
    }

    /**
     * @return DynamoDbClient
     */
    public static function dynamoDb(): DynamoDbClient
    {
        $config = self::config();

        if (isset($_ENV['ENDPOINT'])) {
            $config['endpoint'] = $_ENV['ENDPOINT'];
        }

        $config['version'] = 'latest';

        return new DynamoDbClient($config);
    }
}
