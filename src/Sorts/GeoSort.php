<?php

declare(strict_types=1);

namespace SeyVillas\ElasticQueryBuilder\Sorts;

use SeyVillas\ElasticQueryBuilder\Geo\Point;
use SeyVillas\ElasticQueryBuilder\Geo\Units;

class GeoSort implements ISort
{
    use Units;

    public const ASC = 'asc';
    public const DESC = 'desc';

    // Distance Types
    public const ARC = 'arc';
    public const PLANE = 'plane';


    protected string $field;

    protected string $order;

    protected ?string $missing = null;

    protected ?string $unmappedType = null;

    protected bool $ignoreUnmapped = true;

    protected string $unit = 'km';
    protected string $mode = 'min';
    protected string $distanceType = 'arc';

    protected ?Point $point = null;



    public static function create(
        string $field,
        Point $point,
        string $unit = self::KILOMETERS,
        string $order = self::ASC,
        string $distanceType = self::ARC,

    ): static
    {
        return new self(
            $field,
            $point,
            $unit,
            $order,
            $distanceType
        );
    }

    public function __construct(
        string $field,
        Point $point,
        string $unit,
        string $order,
        string $distanceType
    ) {
        $this->field = $field;
        $this->point = $point;
        $this->unit = $unit;
        $this->order = $order;
        $this->distanceType = $distanceType;
    }

    public function missing(string $missing): static
    {
        $this->missing = $missing;

        return $this;
    }

    public function unmappedType(string $unmappedType): static
    {
        $this->unmappedType = $unmappedType;

        return $this;
    }

    public function ignoreUnmapped(bool $ignoreUnmapped): static
    {
        $this->ignoreUnmapped = $ignoreUnmapped;

        return $this;
    }

    public function toArray(): array
    {
        $payload = [
            $this->field => $this->point,
            'order' => $this->order,
            'unit' => $this->unit,
            'mode' => $this->mode,
            'distance_type' => $this->distanceType,
        ];

        if ($this->missing) {
            $payload['missing'] = $this->missing;
        }

        if ($this->unmappedType) {
            $payload['unmapped_type'] = $this->unmappedType;
        }

        if ($this->ignoreUnmapped) {
            $payload['ignore_unmapped'] = $this->ignoreUnmapped;
        }

        return [
            '_geo_distance' => $payload,
        ];
    }
}
