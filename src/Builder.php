<?php

namespace SeyVillas\ElasticQueryBuilder;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use SeyVillas\ElasticQueryBuilder\Aggregations\Aggregation;
use SeyVillas\ElasticQueryBuilder\Queries\BoolQuery;
use SeyVillas\ElasticQueryBuilder\Queries\Query;
use SeyVillas\ElasticQueryBuilder\Sorts\ISort;

use function array_merge;


class Builder
{
    protected ?BoolQuery $query = null;

    protected ?AggregationCollection $aggregations = null;

    protected ?SortCollection $sorts = null;

    protected ?string $searchIndex = null;

    protected ?int $size = null;

    protected ?int $from = null;

    protected ?array $searchAfter = null;

    protected ?array $fields = null;

    protected bool $withAggregations = true;

    protected bool $trackTotalHits = false;

    protected ?array $scripts = null;

    public function __construct(protected Client $client)
    {
    }

    public function addQuery(Query $query, string $boolType = 'must'): static
    {
        if (! $this->query) {
            $this->query = new BoolQuery();
        }

        $this->query->add($query, $boolType);

        return $this;
    }

    public function addAggregation(Aggregation $aggregation): static
    {
        if (! $this->aggregations) {
            $this->aggregations = new AggregationCollection();
        }

        $this->aggregations->add($aggregation);

        return $this;
    }

    public function addSort(ISort $sort): static
    {
        if (! $this->sorts) {
            $this->sorts = new SortCollection();
        }

        $this->sorts->add($sort);

        return $this;
    }

    public function search(): Elasticsearch|Promise
    {
        $payload = $this->getPayload();

        $params = [
            'body' => $payload,
        ];

        if ($this->searchIndex) {
            $params['index'] = $this->searchIndex;
        }

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        if ($this->from !== null) {
            $params['from'] = $this->from;
        }

        if($this->trackTotalHits) {
            $params['track_total_hits'] = true;
        }

        return $this->client->search($params);
    }

    public function index(string $searchIndex): static
    {
        $this->searchIndex = $searchIndex;

        return $this;
    }

    public function trackTotalHits(bool $value = true): static
    {
        $this->trackTotalHits = $value;

        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function from(int $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function searchAfter(?array $searchAfter): static
    {
        $this->searchAfter = $searchAfter;

        return $this;
    }

    public function fields(array $fields): static
    {
        $this->fields = array_merge($this->fields ?? [], $fields);

        return $this;
    }

    public function withoutAggregations(): static
    {
        $this->withAggregations = false;

        return $this;
    }

    public function addScript(string $name , array $script): static
    {
        $this->scripts[$name]['script'] = $script;

        return $this;
    }

    public function getPayload(): array
    {
        $payload = [];

        if ($this->query) {
            $payload['query'] = $this->query->toArray();
        }

        if ($this->withAggregations && $this->aggregations) {
            $payload['aggs'] = $this->aggregations->toArray();
        }

        if ($this->sorts) {
            $payload['sort'] = $this->sorts->toArray();
        }

        if ($this->fields) {
            $payload['_source'] = $this->fields;
        }

        if ($this->searchAfter) {
            $payload['search_after'] = $this->searchAfter;
        }

        if ($this->scripts) {
            $payload['script_fields'] = $this->scripts;
        }

        return $payload;
    }
}
