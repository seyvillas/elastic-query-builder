<?php

namespace SeyVillas\ElasticQueryBuilder;

use SeyVillas\ElasticQueryBuilder\Sorts\ISort;

class SortCollection
{
    protected array $sorts;

    public function __construct(ISort ...$sorts)
    {
        $this->sorts = $sorts;
    }

    public function add(ISort $sort): self
    {
        $this->sorts[] = $sort;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->sorts);
    }

    public function toArray(): array
    {
        $sorts = [];

        foreach ($this->sorts as $sort) {
            $sorts[] = $sort->toArray();
        }

        return $sorts;
    }
}
