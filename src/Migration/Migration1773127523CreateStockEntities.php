<?php declare(strict_types=1);

namespace StockOutAnalyzer\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1773127523CreateStockEntities extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773127523;
    }

    public function update(Connection $connection): void
    {
        // 1. Prediction Entity
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `stock_prediction` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `prediction_date` DATETIME(3) NULL,
                `daily_sales_velocity` DOUBLE NOT NULL DEFAULT 0.0,
                `confidence_score` DOUBLE NOT NULL DEFAULT 0.0,
                `risk_level` VARCHAR(255) NOT NULL,
                `last_calculated_at` DATETIME(3) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.stock_prediction.product_id` FOREIGN KEY (`product_id`)
                    REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        // 2. ClickHouse Sync State
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `clickhouse_sync_state` (
                `id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NOT NULL,
                `synced_at` DATETIME(3) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.sync_state.order_id` (`order_id`),
                CONSTRAINT `fk.sync_state.order_id` FOREIGN KEY (`order_id`)
                    REFERENCES `order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        // 3. Model Logs
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `stock_model_log` (
                `id` BINARY(16) NOT NULL,
                `model_type` VARCHAR(255) NOT NULL,
                `mean_absolute_error` DOUBLE NOT NULL,
                `training_set_size` INT(11) NOT NULL,
                `execution_time` DOUBLE NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
