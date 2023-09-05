<?php

namespace SeyVillas\ElasticQueryBuilder\Aggregations;

use SeyVillas\ElasticQueryBuilder\Aggregations\Concerns\WithMissing;

class AvgAggregation extends Aggregation
{
    use WithMissing;

    protected string $field;

    public static function create(string $name, string $field): self
    {
        return new self($name, $field);
    }

    public function __construct(string $name, string $field)
    {
        $this->name = $name;
        $this->field = $field;
    }

    public function payload(): array
    {
        $parameters = [
            'field' => $this->field,
        ];

        if ($this->missing) {
            $parameters['missing'] = $this->missing;
        }

        return [
            'avg' => $parameters,
        ];
    }
}
