<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\ClickhouseSyncState;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ClickhouseSyncStateDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'clickhouse_sync_state';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ClickhouseSyncStateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ClickhouseSyncStateCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new DateTimeField('synced_at', 'syncedAt'))->addFlags(new ApiAware(), new Required()),

            (new OneToOneAssociationField('order', 'order_id', 'id', OrderDefinition::class, false))->addFlags(new ApiAware()),
        ]);
    }
}
