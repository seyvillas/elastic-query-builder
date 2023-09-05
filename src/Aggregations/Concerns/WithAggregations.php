<?php

namespace SeyVillas\ElasticQueryBuilder\Aggregations\Concerns;

use SeyVillas\ElasticQueryBuilder\AggregationCollection;
use SeyVillas\ElasticQueryBuilder\Aggregations\Aggregation;

trait WithAggregations
{
    protected AggregationCollection $aggregations;

    public function aggregation(Aggregation $aggregation): self
    {
        $this->aggregations->add($aggregation);

        return $this;
    }
}
