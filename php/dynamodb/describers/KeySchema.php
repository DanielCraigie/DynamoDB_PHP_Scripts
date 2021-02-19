<?php

namespace App\dynamodb\describers;

use App\dynamodb\describers\exceptions\KeySchemaException;

class KeySchema
{
    const PRIMARY_KEY_HASH = 'HASH'; // unique value in table
    const PRIMARY_KEY_RANGE = 'RANGE'; //
    const SECONDARY_INDEX_LOCAL = 'local';
    const SECONDARY_INDEX_GLOBAL = 'global';

    /**
     * @var string[]
     */
    public static $primaryTypes = [
        self::PRIMARY_KEY_HASH,
        self::PRIMARY_KEY_RANGE,
    ];

    /**
     * @var array
     */
    private $schema = [];

    public function __construct(array $config)
    {
        foreach ($config as $attribute) {
            if (empty($attribute['AttributeName'])
                || empty($attribute['KeyType'])
                || !in_array($attribute['KeyType'], self::$primaryTypes)
            ) {
                throw new KeySchemaException(__CLASS__ . ' missing attribute name or key type.');
            }
        }

        $this->schema = $config;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->schema;
    }
}
