<?php

namespace SeyVillas\ElasticQueryBuilder\Aggregations;

class RangeAggregation extends Aggregation
{
    protected string $field;
    protected array $ranges;

    public static function create(string $name, string $field, array $ranges = []): self
    {
        return new self($name, $field, $ranges);
    }
    
    public function __construct(string $name, string $field, array $ranges = [])
    {
        $this->name = $name;
        $this->field = $field;
        $this->ranges = $ranges;
    }

    public function addRange(?float $from, ?float $to, ?string $key = null): self
    {
        $range = [];

        if ($key !== null) {
            $range['key'] = $key;
        }

        if ($from !== null) {
            $range['from'] = $from;
        }

        if ($to !== null) {
            $range['to'] = $to;
        }

        $this->ranges[] = $range;

        return $this;
    }

    public function payload(): array
    {
        return [
            'range' => [
                'field' => $this->field,
                'ranges' => $this->ranges,
            ],
        ];
    }
}
