<?php

namespace SeyVillas\ElasticQueryBuilder\Queries;

use SeyVillas\ElasticQueryBuilder\Exceptions\BoolQueryTypeDoesNotExist;

class BoolQuery implements Query
{
    public const MUST = 'must';
    public const FILTER = 'filter';
    public const SHOULD = 'should';
    public const MUST_NOT = 'must_not';

    protected array $must = [];
    protected array $filter = [];
    protected array $should = [];
    protected array $must_not = [];

    public static function create(): static
    {
        return new self();
    }

    public function add(Query $query, string $type = self::MUST): static
    {
        if (! in_array($type, [static::MUST, static::FILTER, static::SHOULD, static::MUST_NOT])) {
            throw new BoolQueryTypeDoesNotExist($type);
        }

        $this->$type[] = $query;

        return $this;
    }

    public function toArray(): array
    {
        $bool = [
            static::MUST => array_map(fn (Query $query) => $query->toArray(), $this->must),
            static::FILTER => array_map(fn (Query $query) => $query->toArray(), $this->filter),
            static::SHOULD => array_map(fn (Query $query) => $query->toArray(), $this->should),
            static::MUST_NOT => array_map(fn (Query $query) => $query->toArray(), $this->must_not),
        ];

        return [
            'bool' => array_filter($bool),
        ];
    }
}
