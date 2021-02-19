<?php

namespace App\dynamodb\describers;

use Aws\Api\DateTimeResult;

class ProvisionedThroughput
{
    /**
     * @var DateTimeResult
     */
    private $lastIncreaseDateTime;

    /**
     * @var DateTimeResult
     */
    private $lastDecreaseDateTime;

    /**
     * @var
     */
    private $numberOfDecreasesToday = 0;

    /**
     * @var int
     */
    private $readCapacityUnits = 5;

    /**
     * @var int
     */
    private $writeCapacityUnits = 5;

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->{lcfirst($key)} = $value;
        }
    }

    /**
     * @return DateTimeResult|null
     */
    public function getLastIncreaseDateTime(): ?DateTimeResult
    {
        return $this->lastIncreaseDateTime;
    }

    /**
     * @return DateTimeResult|null
     */
    public function getLastDecreaseDateTime(): ?DateTimeResult
    {
        return $this->lastDecreaseDateTime;
    }

    /**
     * @return int
     */
    public function getNumberOfDecreasesToday(): int
    {
        return $this->numberOfDecreasesToday;
    }

    /**
     * @return int
     */
    public function getReadCapacityUnits(): int
    {
        return $this->readCapacityUnits;
    }

    /**
     * @return int
     */
    public function getWriteCapacityUnits(): int
    {
        return $this->writeCapacityUnits;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ReadCapacityUnits' => $this->readCapacityUnits,
            'WriteCapacityUnits' => $this->writeCapacityUnits,
        ];
    }
}
