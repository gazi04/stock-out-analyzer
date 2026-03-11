<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use ClickHouseDB\Client;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clickhouse:bulk-ingest',
    description: 'Chunks historical MySQL data and bulk-inserts into ClickHouse.'
)]
class BulkIngestSalesCommand extends Command
{
    private Connection $connection;
    private Client $clickHouse;
    private int $chunkSize = 5000;

    public function __construct(Connection $connection, Client $clickHouse)
    {
        parent::__construct();
        $this->connection = $connection;
        $this->clickHouse = $clickHouse;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting Bulk Ingestion to ClickHouse');

        try {
            $this->ingestSalesFacts($io, $output);
            $this->ingestStockSnapshots($io, $output);
            
            $io->success('Bulk ingestion completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Ingestion failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function ingestSalesFacts(SymfonyStyle $io, OutputInterface $output): void
    {
        $io->section('1. Ingesting Sales Data');
        
        $lastSync = $this->connection->fetchOne(
            "SELECT last_sync_date FROM clickhouse_sync_state WHERE entity_name = 'sales_fact' LIMIT 1"
        );

        $lastSync = $lastSync ?: '2000-01-01 00:00:00';
        $io->note("Syncing records newer than: " . $lastSync);

        $totalRows = (int) $this->connection->fetchOne(
            "SELECT COUNT(oli.id) FROM order_line_item oli 
             INNER JOIN `order` o ON o.id = oli.order_id 
             WHERE oli.type = 'product' AND o.order_date_time > ?", 
            [$lastSync]
        );

        if ($totalRows === 0) {
            $io->success('Already up to date!');
            return;
        }
        
        $progressBar = new ProgressBar($output, $totalRows);
        $progressBar->start();

        $offset = 0;
        
        while ($offset < $totalRows) {
            // Raw DBAL Query for maximum performance
            $sql = "
                SELECT 
                    oli.order_id,
                    oli.product_id,
                    o.order_date_time,
                    p.product_number,
                    (SELECT category_id FROM product_category pc WHERE pc.product_id = p.id LIMIT 1) as category_id,
                    oli.quantity,
                    oli.unit_price,
                    oli.total_price,
                    c.iso_code as currency,
                    o.sales_channel_id
                FROM order_line_item oli
                INNER JOIN `order` o ON o.id = oli.order_id
                INNER JOIN product p ON p.id = oli.product_id
                LEFT JOIN currency c ON c.id = o.currency_id
                WHERE oli.type = 'product' AND o.order_date_time > :lastSync
                ORDER BY o.order_date_time ASC
                LIMIT {$this->chunkSize} OFFSET {$offset}
            ";

            $rows = $this->connection->fetchAllAssociative($sql);
            $clickHouseData = [];

            foreach ($rows as $row) {
                $formatedDate = substr((string)$row['order_date_time'], 0, 19);

                // Transform binary UUIDs to standard UUID strings for ClickHouse
                $clickHouseData[] = [
                    Uuid::fromBytesToHex($row['order_id']),
                    Uuid::fromBytesToHex($row['product_id']),
                    $formatedDate,
                    $row['product_number'] ?? 'UNKNOWN',
                    $row['category_id'] ? Uuid::fromBytesToHex($row['category_id']) : '', // Used as category_path
                    (int) $row['quantity'],
                    (float) $row['unit_price'],
                    (float) $row['total_price'],
                    $row['currency'] ?? 'EUR',
                    Uuid::fromBytesToHex($row['sales_channel_id'])
                ];
            }

            if (!empty($clickHouseData)) {
                $this->clickHouse->insert(
                    'shopware.sales_fact',
                    $clickHouseData,
                    ['order_id', 'product_id', 'order_date', 'product_number', 'category_path', 'quantity', 'unit_price', 'total_price', 'currency', 'sales_channel_id']
                );

                $latestInBatch = end($rows)['order_date_time'];
                if ($latestInBatch > $newestTimestamp) {
                    $newestTimestamp = $latestInBatch;
                }
            }

            $offset += $this->chunkSize;
            $progressBar->advance(count($rows));
        }

        $this->connection->executeStatement(
            "REPLACE INTO clickhouse_sync_state (entity_name, last_sync_date) VALUES ('sales_fact', ?)",
            [$newestTimestamp]
        );

        $progressBar->finish();
        $io->newLine(2);
    }

    private function ingestStockSnapshots(SymfonyStyle $io, OutputInterface $output): void
    {
        $io->section('2. Generating Day 0 Stock Snapshots');

        $totalRows = (int) $this->connection->fetchOne("SELECT COUNT(id) FROM product WHERE parent_id IS NULL OR parent_id != id");
        
        if ($totalRows === 0) return;

        $progressBar = new ProgressBar($output, $totalRows);
        $progressBar->start();

        $offset = 0;
        $today = date('Y-m-d');

        while ($offset < $totalRows) {
            $sql = "
                SELECT id, stock, available_stock, min_purchase, purchase_steps 
                FROM product 
                LIMIT {$this->chunkSize} OFFSET {$offset}
            ";

            $rows = $this->connection->fetchAllAssociative($sql);
            $clickHouseData = [];

            foreach ($rows as $row) {
                $clickHouseData[] = [
                    Uuid::fromBytesToHex($row['id']),
                    $today,
                    (int) $row['stock'],
                    (int) $row['available_stock'],
                    (int) ($row['min_purchase'] ?? 1),
                    (int) ($row['purchase_steps'] ?? 1)
                ];
            }

            if (!empty($clickHouseData)) {
                $this->clickHouse->insert(
                    'shopware.stock_snapshots',
                    $clickHouseData,
                    ['product_id', 'snapshot_date', 'current_stock', 'available_stock', 'min_purchase', 'purchase_steps']
                );
            }

            $offset += $this->chunkSize;
            $progressBar->advance(count($rows));
        }

        $progressBar->finish();
        $io->newLine(2);
    }
}
