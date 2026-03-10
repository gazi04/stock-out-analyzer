<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockModelLog;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class StockModelLogEntity extends Entity
{
    use EntityIdTrait;

    protected string $modelType;

    protected float $meanAbsoluteError;

    protected int $trainingSetSize;

    protected float $executionTime;

    public function getModelType(): string
    {
        return $this->modelType;
    }

    public function setModelType(string $modelType): void
    {
        $this->modelType = $modelType;
    }

    public function getMeanAbsoluteError(): float
    {
        return $this->meanAbsoluteError;
    }

    public function setMeanAbsoluteError(float $meanAbsoluteError): void
    {
        $this->meanAbsoluteError = $meanAbsoluteError;
    }

    public function getTrainingSetSize(): int
    {
        return $this->trainingSetSize;
    }

    public function setTrainingSetSize(int $trainingSetSize): void
    {
        $this->trainingSetSize = $trainingSetSize;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(float $executionTime): void
    {
        $this->executionTime = $executionTime;
    }
}
