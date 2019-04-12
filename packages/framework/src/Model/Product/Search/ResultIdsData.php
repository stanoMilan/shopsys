<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ResultIdsData
{
    /**
     * @var int
     */
    protected $total;

    /**
     * @var array
     */
    protected $ids;

    /**
     * @param int $total
     * @param int[] $ids
     */
    public function __construct(int $total, array $ids)
    {
        $this->total = $total;
        $this->ids = $ids;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
