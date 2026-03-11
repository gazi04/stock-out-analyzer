<?php declare(strict_types=1);

namespace StockOutAnalyzer\Scheduler;

use StockOutAnalyzer\Message\ClickHouseIngestMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('clickhouse_sync')]
class DailyIngestSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                // This triggers the message once every day
                RecurringMessage::every('1 day', new ClickHouseIngestMessage())
            );
    }
}
