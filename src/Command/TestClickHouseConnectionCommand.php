<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use ClickHouseDB\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clickhouse:test-connection',
    description: 'Tests the connection to ClickHouse server.'
)]
class TestClickHouseConnectionCommand extends Command
{
    protected static $defaultName = 'clickhouse:test-connection';
    private Client $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($this->client->ping()) {
                $io->success('Successfully connected to ClickHouse!');
            }
        } catch (\Exception $e) {
            $io->error('Connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
