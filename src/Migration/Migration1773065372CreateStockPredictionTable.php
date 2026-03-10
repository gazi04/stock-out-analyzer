<?php declare(strict_types=1);

namespace StockOutAnalyzer\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1773065372CreateStockPredictionTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773065372;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `stock_prediction` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `prediction_date` DATETIME(3) NOT NULL,
                `daily_sales_velocity` DOUBLE NOT NULL,
                `confidence_score` DOUBLE NOT NULL,
                `risk_level` VARCHAR(255) NOT NULL,
                `last_calculated_at` DATETIME(3) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.stock_prediction.product_id` FOREIGN KEY (`product_id`)
                    REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
