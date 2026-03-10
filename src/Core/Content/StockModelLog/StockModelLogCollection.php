<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockModelLog;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<StockModelLogEntity>
 */
class StockModelLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return StockModelLogEntity::class;
    }
}
