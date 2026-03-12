<?php declare(strict_types=1);

namespace StockOutAnalyzer\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'stock:generate-history', description: 'Generates realistic historical sales data')]
class GenerateHistoricalOrdersCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly EntityRepository $stateMachineStateRepository,
        private readonly EntityRepository $countryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        // 1. Fetch reference data needed to build a valid order
        $salesChannelId = $this->salesChannelRepository->searchIds(new Criteria(), $context)->firstId();
        $customerId = $this->customerRepository->searchIds(new Criteria(), $context)->firstId();
        $countryId = $this->countryRepository->searchIds(new Criteria(), $context)->firstId();
        
        // Fetch the "Completed" state ID for orders so your analyzer counts them as real sales
        $stateCriteria = (new Criteria())->addFilter(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter('technicalName', 'completed'));
        $stateId = $this->stateMachineStateRepository->searchIds($stateCriteria, $context)->firstId();

        // Fetch a pool of up to 100 real products
        $productCriteria = new Criteria();
        $productCriteria->setLimit(100);
        $products = $this->productRepository->search($productCriteria, $context)->getEntities();

        if ($products->count() === 0) {
            $output->writeln('<error>No products found in the database!</error>');
            return Command::FAILURE;
        }

        // 2. Setup the Date Loop (e.g., January 2024)
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $interval = new \DateInterval('P1D'); // 1 Day interval
        $period = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        $ordersPayload = [];
        $totalOrdersGenerated = 0;

        $output->writeln('Starting realistic order generation...');

        // 3. Loop through each day
        foreach ($period as $date) {
            // Randomize how many orders happen on this specific day (e.g., between 15 and 50)
            $ordersToday = rand(15, 50);
            
            for ($i = 0; $i < $ordersToday; $i++) {
                // Randomize time of day (between 08:00 and 22:00)
                $orderDateTime = clone $date;
                $orderDateTime->setTime(rand(8, 22), rand(0, 59), rand(0, 59));

                // 4. Build Random Line Items & Calculate Totals
                $lineItems = [];
                $orderTotalPrice = 0.0;
                $numberOfItemsInOrder = rand(1, 4); // 1 to 4 different products per order

                for ($j = 0; $j < $numberOfItemsInOrder; $j++) {
                    // Pick a random product from our pool
                    $randomProduct = $products->getElements()[array_rand($products->getElements())];
                    $qty = rand(1, 3);
                    
                    // Safely get price (fallback to 10 if missing for some reason)
                    $unitPrice = $randomProduct->getPrice()?->first()?->getGross() ?? 10.0;
                    $totalLinePrice = $unitPrice * $qty;
                    $orderTotalPrice += $totalLinePrice;

                    $lineItems[] = [
                        'id' => Uuid::randomHex(),
                        'identifier' => $randomProduct->getId(),
                        'productId' => $randomProduct->getId(),
                        'quantity' => $qty,
                        'label' => $randomProduct->getName() ?? 'Product',
                        'price' => [
                            'unitPrice' => $unitPrice,
                            'totalPrice' => $totalLinePrice,
                            'quantity' => $qty,
                            'calculatedTaxes' => [],
                            'taxRules' => [],
                        ],
                    ];
                }

                $addressId = Uuid::randomHex();

                // 5. Construct the full Order Payload
                $ordersPayload[] = [
                    'id' => Uuid::randomHex(),
                    'orderNumber' => 'HIST-' . rand(10000, 99999),
                    'orderDateTime' => $orderDateTime->format(DATE_ATOM),
                    'stateId' => $stateId ?? Uuid::randomHex(),
                    'currencyId' => $context->getCurrencyId(),
                    'currencyFactor' => 1.0,
                    'salesChannelId' => $salesChannelId,
                    'billingAddressId' => $addressId,
                    'addresses' => [
                        [
                            'id' => $addressId,
                            'firstName' => 'History',
                            'lastName' => 'Customer',
                            'street' => 'Mock Street 1',
                            'zipcode' => '12345',
                            'city' => 'Mock City',
                            'countryId' => $countryId,
                        ]
                    ],
                    'price' => [
                        'netPrice' => $orderTotalPrice * 0.81,
                        'totalPrice' => $orderTotalPrice,
                        'rawTotal' => $orderTotalPrice,
                        'positionPrice' => $orderTotalPrice,
                        'taxStatus' => 'gross',
                        'calculatedTaxes' => [],
                        'taxRules' => [],
                    ],
                    'itemRounding' => [
                        'decimals' => 2,
                        'interval' => 0.01,
                        'roundForNet' => true
                    ],
                    'totalRounding' => [
                        'decimals' => 2,
                        'interval' => 0.01,
                        'roundForNet' => true
                    ],
                    'shippingCosts' => [
                        'unitPrice' => 0, 'totalPrice' => 0, 'quantity' => 1, 'calculatedTaxes' => [], 'taxRules' => []
                    ],
                    'orderCustomer' => [
                        'customerId' => $customerId,
                        'email' => 'history' . rand(1, 1000) . '@example.com',
                        'firstName' => 'History',
                        'lastName' => 'Customer',
                    ],
                    'lineItems' => $lineItems
                ];

                $totalOrdersGenerated++;

                // 6. Batch Insert (Write to DB every 50 orders to save RAM)
                if (count($ordersPayload) >= 50) {
                    $this->orderRepository->create($ordersPayload, $context);
                    $ordersPayload = []; // Reset array
                    $output->write('.'); // Print dot for progress
                }
            }
        }

        // Insert any remaining orders
        if (!empty($ordersPayload)) {
            $this->orderRepository->create($ordersPayload, $context);
        }

        $output->writeln("\n<info>Success: Generated {$totalOrdersGenerated} historical orders!</info>");
        return Command::SUCCESS;
    }
}
