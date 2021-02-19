<?php

namespace App\dynamodb;

use App\dynamodb\exceptions\DynamoAttributeException;

class Attribute
{
    const TYPE_BINARY = 'B';
    const TYPE_BOOL = 'BOOL';
    const TYPE_BINARY_SET = 'BS';
    const TYPE_LIST = 'L';
    const TYPE_MAP = 'M';
    const TYPE_NUMBER = 'N';
    const TYPE_NUMBER_SET = 'NS';
    const TYPE_NULL = 'NULL';
    const TYPE_STRING = 'S';
    const TYPE_STRING_SET = 'SS';

    public static $validTypes = [
        self::TYPE_BINARY,
        self::TYPE_BOOL,
        self::TYPE_BINARY_SET,
        self::TYPE_LIST,
        self::TYPE_MAP,
        self::TYPE_NUMBER,
        self::TYPE_NUMBER_SET,
        self::TYPE_NULL,
        self::TYPE_STRING,
        self::TYPE_STRING_SET,
    ];

    /**
     * @var string
     */
    private $name;

    /**
     * @var
     */
    private $type;

    /**
     * @var
     */
    private $value;

    /**
     * @var bool
     */
    private $primaryKey = false;

    /**
     * @param array $definition
     * @return static
     * @throws DynamoAttributeException
     */
    public static function createFromArray(array $definition): self
    {
        if (empty($definition['AttributeName'])
            || empty($definition['AttributeType'])
            || !in_array($definition['AttributeType'], self::$validTypes)
        ) {
            throw new DynamoAttributeException('Missing definition element in array');
        }

        return new self($definition['AttributeName'], null, $definition['AttributeType']);
    }

    public function __construct(string $name, $value, string $type = self::TYPE_STRING)
    {
        if (empty($name)) {
            throw new DynamoAttributeException(__CLASS__ . ' must be given a Name');
        }

        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

}
