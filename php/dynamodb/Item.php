<?php

namespace App\dynamodb;

use App\dynamodb\exceptions\DynamoAttributeException;
use Aws\Result;

class Item
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $tablePrimaryKey = [];

    /**
     * Item constructor.
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        var_dump($table);die;
    }

    /**
     * @param $attribute
     * @return Attribute|null
     */
    public function __get(string $attribute): ?Attribute
    {
        if (!isset($this->attributes[$attribute])) {
            return null;
        }

        return $this->attributes[$attribute];
    }

    /**
     * @param $attribute
     * @param null $value
     * @throws DynamoAttributeException
     */
    public function __set($attribute, $value = null)
    {
        $this->setAttribute($attribute, $value);
    }

    /**
     * @param $attribute
     * @param null $value
     * @param null $type
     * @return bool
     * @throws DynamoAttributeException
     */
    public function setAttribute($attribute, $value = null, $type = null): bool
    {
        if (!$attribute instanceof Attribute) {
            $attribute = new Attribute($attribute, $value, $type);
        }

        $this->attributes[$attribute->getName()] = $attribute;

        return true;
    }

    /**
     * @return bool
     */
    private function hasPrimaryKey(): bool
    {
        foreach ($this->table->getKeyAttributes() as $keyAttribute) {
            if (empty($this->attributes[$keyAttribute])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function put(): bool
    {
        // ensure primary key is defined
        if (!$this->hasPrimaryKey()) {
            throw new \Exception(__CLASS__ . ' missing Primary Key required for table ' . $this->table->getName());
        }

        $this->table->putItem([
            'TableName' => $this->table->getName(),
            'Item' => $this->putAttributes(),
        ]);

        return true;
    }

    /**
     * @return bool
     */
    public function get(): bool
    {
        $query = [];

        foreach ($this->table->getKeyAttributes() as $keyAttribute) {
            $query[$keyAttribute] = [
                'S' => $this->attributes[$keyAttribute],
            ];
        }

        $result = $this->table->getItem([
            'TableName' => $this->table->getName(),
            'Key' => $query,
        ]);

        if (!$result instanceof Result) {
            return false;
        }

        foreach ($result['Item'] as $attribute => $value) {
            $this->attributes[$attribute] = reset($value);
        }

        return true;
    }
}
