<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clean:order',
    description: 'Instantly cleans the order and order_line_item tables using raw SQL.'
)]
class CleanOrderTableDemoDataCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting rapid database cleanup');

        try {
            // Count existing orders for the output message
            $count = (int) $this->connection->fetchOne('SELECT COUNT(id) FROM `order`');

            if ($count === 0) {
                $io->info('No orders found to delete.');
                return Command::SUCCESS;
            }

            if (!$io->confirm(sprintf('Are you sure you want to delete %d orders AND all their line items?', $count), false)) {
                $io->warning('Action cancelled.');
                return Command::SUCCESS;
            }

            // A raw SQL DELETE triggers MySQL's ON DELETE CASCADE.
            // This instantly wipes order_line_item, order_delivery, and order_transaction.
            $this->connection->executeStatement('DELETE FROM `order`');

            $io->success(sprintf('Successfully purged %d orders from the database.', $count));
            $io->note('Run "bin/console dal:refresh:index" to update customer statistics.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Cleaning order table failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
