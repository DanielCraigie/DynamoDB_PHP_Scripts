<?php

namespace App\dynamodb;

use App\dynamodb\describers\KeySchema;
use App\dynamodb\describers\ProvisionedThroughput;
use Aws\Crypto\Polyfill\Key;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Exception;

class Table
{
    /**
     * @var \Aws\DynamoDb\DynamoDbClient
     */
    private $client;

    /**
     * @var String
     */
    private $name;

    /**
     * @var Result
     */
    private $description;

    /**
     * @var array
     */
    private $attributeDefinitions = [];

    /**
     * @var KeySchema
     */
    private $keySchema;

    /**
     * @var ProvisionedThroughput
     */
    private $provisionedThroughput;

    public function __construct(String $name)
    {
        $this->client = \App\ClientFactory::dynamoDb();
        $this->name = $name;

        $description = $this->describe();
        if ($description instanceof Result) {
            foreach ($description['Table']['AttributeDefinitions'] as $attributeDefinition) {
                $this->attributeDefinitions[] = Attribute::createFromArray($attributeDefinition);
            }

            $this->keySchema = new KeySchema($description['Table']['KeySchema']);
            $this->provisionedThroughput = new ProvisionedThroughput($description['Table']['ProvisionedThroughput']);
        }
    }

    /**
     * @param $attributes
     * @param $keys
     * @return Result
     */
    public function create($attributes, $keys): Result
    {
        $config = [
            'TableName' => $this->name,
            'ProvisionedThroughput' => $this->provisionedThroughput->toArray(),
            'KeySchema' => $this->keySchema->toArray(),
        ];

        foreach ($attributes as $attribute => $type) {
            $config['AttributeDefinitions'][] = [
                'AttributeName' => $attribute,
                'AttributeType' => $type,
            ];
        }

        $result = $this->client->createTable($config);

        $this->keySchema = $result['TableDescription']['KeySchema'];

        return $result;
    }

    public function update()
    {

    }

    /**
     * @return Result
     */
    public function delete()
    {
        return $this->client->deleteTable([
            'TableName' => $this->name,
        ]);
    }

    /**
     * @return Result
     * @throws Exception
     */
    private function describe()
    {
        if (!$this->description instanceof Result) {
            $this->description = $this->client->describeTable(['TableName' => $this->name]);
        }

        return $this->description;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function exists(): bool
    {
        try {
            $result = $this->describe();

            if (!$result instanceof Result
                || $result['Table']['TableStatus'] != 'ACTIVE'
            ) {
                return false;
            }

            return true;
        } catch (DynamoDbException $e) {
            if (preg_match('/ResourceNotFoundException/', $e->getMessage())) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getKeyAttributes(): array
    {
        return array_column($this->keySchema, 'AttributeName');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $item
     * @return bool
     */
    public function putItem(array $item): bool
    {
        try {
            $this->client->putItem($item);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param array $query
     * @return Result|null
     */
    public function getItem(array $query): ?Result
    {
        try {
            return $this->client->getItem($query);
        } catch (Exception $e) {
            return null;
        }
    }
}
