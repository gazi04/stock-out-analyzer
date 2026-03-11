<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Regressors\KDNeighborsRegressor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clickhouse:test-rubix-ml',
    description: 'Tests if Rubix ML is correctly installed and working.'
)]
class TestRubixMLCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing Rubix ML Integration');

        try {
            // Simple test: Create a dataset and a regressor
            $samples = [
                [0.1, 20],
                [0.2, 30],
                [0.3, 40],
            ];

            $dataset = new Unlabeled($samples);
            $estimator = new KDNeighborsRegressor();

            $io->note('Samples: ' . count($dataset->samples()));
            $io->note('Estimator: ' . get_class($estimator));

            if (extension_loaded('tensor')) {
                $io->success('Rubix Tensor extension is LOADED and providing hardware acceleration!');
            } else {
                $io->warning('Rubix Tensor extension is NOT loaded. Falling back to PHP implementation (slower).');
            }

            $io->success('Rubix ML is installed and working!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Rubix ML test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
