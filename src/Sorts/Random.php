<?php

namespace SeyVillas\ElasticQueryBuilder\Sorts;

class Random implements ISort
{
    public static function create(): static
    {
        return new self();
    }

    public function toArray(): array
    {
        return [
            "_script" => [
                "type" => "number",
                "script" => [
                    "lang" => "painless",
                    "source" => "Math.random()"
                ],
                "order" => "asc"
            ],
        ];
    }
}
