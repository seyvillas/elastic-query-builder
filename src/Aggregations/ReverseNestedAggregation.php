<?php

namespace SeyVillas\ElasticQueryBuilder\Aggregations;

use SeyVillas\ElasticQueryBuilder\AggregationCollection;
use SeyVillas\ElasticQueryBuilder\Aggregations\Concerns\WithAggregations;
use stdClass;

class ReverseNestedAggregation extends Aggregation
{
    use WithAggregations;

    public static function create(
        string $name,
        Aggregation ...$aggregations
    ): self {
        return new self($name, ...$aggregations);
    }

    public function __construct(
        string $name,
        Aggregation ...$aggregations
    ) {
        $this->name = $name;
        $this->aggregations = new AggregationCollection(...$aggregations);
    }

    public function payload(): array
    {
        return [
            'reverse_nested' => new stdClass(),
            'aggs' => $this->aggregations->toArray(),
        ];
    }
}
