<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clean:order',
    description: 'Clean the order table from demo data.'
)]
class CleanOrderTableDemoDataCommand extends Command
{
    public function __construct(private readonly EntityRepository $orderRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting to clean order table');

        // 1. Get the System Context
        $context = Context::createDefaultContext();

        try {
            $criteria = new Criteria();
            
            // Search for IDs
            $ids = $this->orderRepository->searchIds($criteria, $context)->getIds();
            $count = count($ids);

            if ($count === 0) {
                $io->info('No orders found to delete.');
                return Command::SUCCESS;
            }

            // 2. Add a Confirmation Step (Safety First!)
            if (!$io->confirm(sprintf('Are you sure you want to delete %d orders?', $count), false)) {
                $io->warning('Action cancelled.');
                return Command::SUCCESS;
            }

            // 3. Prepare IDs for deletion
            $idsArray = array_map(static function($id) {
                return ['id' => $id];
            }, $ids);

            // 4. Perform Deletion
            $this->orderRepository->delete($idsArray, $context);

            $io->success(sprintf('Successfully deleted %d orders.', $count));
            
            // 5. Suggest indexing refresh
            $io->note('Run "bin/console dal:refresh:index" to update customer statistics.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Cleaning order table failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
