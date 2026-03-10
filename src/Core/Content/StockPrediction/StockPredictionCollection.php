<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockPrediction;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<StockPredictionEntity>
 */
class StockPredictionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return StockPredictionEntity::class;
    }
}
