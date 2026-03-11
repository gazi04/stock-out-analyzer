<?php declare(strict_types=1);

namespace StockOutAnalyzer\MessageHandler;

use StockOutAnalyzer\Command\BulkIngestSalesCommand;
use StockOutAnalyzer\Message\ClickHouseIngestMessage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ClickHouseIngestHandler
{
    private BulkIngestSalesCommand $command;

    public function __construct(BulkIngestSalesCommand $command)
    {
        $this->command = $command;
    }

    public function __invoke(ClickHouseIngestMessage $message): void
    {
        // We run the command programmatically
        $input = new ArrayInput([]);
        $output = new NullOutput(); // Use NullOutput to keep logs clean, or use a logger
        
        $this->command->run($input, $output);
    }
}
