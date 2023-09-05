<?php

declare(strict_types=1);

namespace SeyVillas\ElasticQueryBuilder\Geo;

use JsonSerializable;

class Point implements JsonSerializable
{
    public static function create(
        float $latitude,
        float $longitude
    ): self {
        return new self($latitude, $longitude);
    }

    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {}

    public function getCoordinates(): array
    {
        return [
            'lat' => $this->latitude,
            'lon' => $this->longitude,
        ];
    }

    public function getGeoPoint(): array
    {
        return [
            'type' => 'Point',
            'coordinates' => [$this->longitude, $this->latitude]
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->getGeoPoint();
    }
}
