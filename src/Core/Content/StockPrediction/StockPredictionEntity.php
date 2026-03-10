<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Content\StockPrediction;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class StockPredictionEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;

    protected string $productVersionId;

    protected ?\DateTimeInterface $predictionDate = null;

    protected float $dailySalesVelocity;

    protected float $confidenceScore;

    protected string $riskLevel;

    protected \DateTimeInterface $lastCalculatedAt;

    protected ?ProductEntity $product = null;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getPredictionDate(): ?\DateTimeInterface
    {
        return $this->predictionDate;
    }

    public function setPredictionDate(?\DateTimeInterface $predictionDate): void
    {
        $this->predictionDate = $predictionDate;
    }

    public function getDailySalesVelocity(): float
    {
        return $this->dailySalesVelocity;
    }

    public function setDailySalesVelocity(float $dailySalesVelocity): void
    {
        $this->dailySalesVelocity = $dailySalesVelocity;
    }

    public function getConfidenceScore(): float
    {
        return $this->confidenceScore;
    }

    public function setConfidenceScore(float $confidenceScore): void
    {
        $this->confidenceScore = $confidenceScore;
    }

    public function getRiskLevel(): string
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(string $riskLevel): void
    {
        $this->riskLevel = $riskLevel;
    }

    public function getLastCalculatedAt(): \DateTimeInterface
    {
        return $this->lastCalculatedAt;
    }

    public function setLastCalculatedAt(\DateTimeInterface $lastCalculatedAt): void
    {
        $this->lastCalculatedAt = $lastCalculatedAt;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }
}
