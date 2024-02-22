<?php

namespace App\Model\Product;

use App\Entity\Team;
use App\Model\Doctrine\Sequence;
use App\Model\Product\Enum\ProductSortable;
use App\Model\Product\Enum\ProductStatus;

class ProductLookupQuery
{
    private string $orderBy, $sequence, $status;
    private ?string $search;
    private int $page, $pageSize;
    private $teams;

    public function __construct(ProductSortable $orderBy, Sequence $sequence, string $search = null, ProductStatus $status = ProductStatus::ALL, int $page = 1, int $pageSize = 10, array $teams = [])
    {
        $this->search = $search;
        $this->orderBy = $orderBy->value;
        $this->sequence = $sequence->value;
        $this->status = $status->value;

        $this->page = $page;
        $this->pageSize = $pageSize;

        $this->setTeams(...$teams);
    }

    public function setTeams(Team ...$teams)
    {
        $this->teams = [];
        foreach ($teams as $team)
            $this->teams[] = $team;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getSequence(): string
    {
        return $this->sequence;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getOffset(): int {
        return ($this->page - 1) * $this->pageSize;
    }

    public function getTeams(): array
    {
        return $this->teams;
    }
}
