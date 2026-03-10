<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\ClickhouseSyncState;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ClickhouseSyncStateEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderId;

    protected \DateTimeInterface $syncedAt;

    protected ?OrderEntity $order = null;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getSyncedAt(): \DateTimeInterface
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(\DateTimeInterface $syncedAt): void
    {
        $this->syncedAt = $syncedAt;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }
}
