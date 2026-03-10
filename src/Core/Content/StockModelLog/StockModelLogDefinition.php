<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockModelLog;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class StockModelLogDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'stock_model_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return StockModelLogEntity::class;
    }

    public function getCollectionClass(): string
    {
        return StockModelLogCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('model_type', 'modelType'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('mean_absolute_error', 'meanAbsoluteError'))->addFlags(new ApiAware(), new Required()),
            (new IntField('training_set_size', 'trainingSetSize'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('execution_time', 'executionTime'))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
