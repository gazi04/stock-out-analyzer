<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\ClickhouseSyncState;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ClickhouseSyncStateEntity>
 */
class ClickhouseSyncStateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ClickhouseSyncStateEntity::class;
    }
}
