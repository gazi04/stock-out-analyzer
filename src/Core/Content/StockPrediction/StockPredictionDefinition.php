<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockPrediction;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class StockPredictionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'stock_prediction';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return StockPredictionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return StockPredictionCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new DateTimeField('prediction_date', 'predictionDate'))->addFlags(new ApiAware()),
            (new FloatField('daily_sales_velocity', 'dailySalesVelocity'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('confidence_score', 'confidenceScore'))->addFlags(new ApiAware(), new Required()),
            (new StringField('risk_level', 'riskLevel'))->addFlags(new ApiAware(), new Required()),
            (new DateTimeField('last_calculated_at', 'lastCalculatedAt'))->addFlags(new ApiAware(), new Required()),

            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}
