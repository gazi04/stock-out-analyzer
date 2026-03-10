<?php declare(strict_types=1);

namespace StockOutAnalyzer\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1773065432CreateStockModelLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773065432;
    }

    public function update(Connection $connection): void
    {

    }
}
