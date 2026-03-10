<?php declare(strict_types=1);

namespace StockOutAnalyzer\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1773127317DeletePluginStockTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773127317;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            DROP TABLE `stock_prediction`;
        ');
    }
}
