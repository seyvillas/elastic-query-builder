<?php

namespace SeyVillas\ElasticQueryBuilder\Queries;

use Spatie\ElasticsearchQueryBuilder\Queries\Query;

class TermQuery implements Query
{
    protected string $field;

    protected string|int|bool $value;

    public static function create(string $field, string|int|bool $value): static
    {
        return new self($field, $value);
    }

    public function __construct(string $field, string|int|bool $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'term' => [
                $this->field => $this->value,
            ],
        ];
    }
}
