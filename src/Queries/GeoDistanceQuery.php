<?php

declare(strict_types=1);

namespace SeyVillas\ElasticQueryBuilder\Queries;

use SeyVillas\ElasticQueryBuilder\Geo\Point;
use SeyVillas\ElasticQueryBuilder\Geo\Units;

class GeoDistanceQuery implements Query
{
    use Units;

    protected string $field;
    protected Point $value;
    protected int|float $distance;
    protected string $unit;

    public static function create(string $field, Point $value, int|float $distance = 10, string $unit = self::KILOMETERS): static
    {
        return new self($field, $value, $distance, $unit);
    }

    public function __construct(
        string $field,
        Point $value,
        int|float $distance = 10,
        string $unit = self::KILOMETERS,
    )
    {
        $this->field = $field;
        $this->value = $value;
        $this->distance = $distance;
        $this->unit = $unit;
    }

    public function toArray(): array
    {
        return [
            'geo_distance' => [
                'distance' => $this->distance . $this->unit,
                $this->field => $this->value->getCoordinates(),
            ]
        ];
    }

    public function getDistance(): int|float
    {
        return $this->distance;
    }
}
