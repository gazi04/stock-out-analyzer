<?php declare(strict_types=1);

namespace StockOutAnalyzer\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1773065450AddProductVersionIdToStockPrediction extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773065450;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `stock_prediction`
            DROP FOREIGN KEY `fk.stock_prediction.product_id`;
        ');

        $connection->executeStatement('
            ALTER TABLE `stock_prediction`
            ADD COLUMN `product_version_id` BINARY(16) NOT NULL AFTER `product_id`,
            ADD CONSTRAINT `fk.stock_prediction.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ');
    }
}
