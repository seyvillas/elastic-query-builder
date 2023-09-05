<?php

namespace SeyVillas\ElasticQueryBuilder\Aggregations\Concerns;

trait WithMissing
{
    protected ?string $missing = null;

    public function missing(string $missingValue): self
    {
        $this->missing = $missingValue;

        return $this;
    }
}
