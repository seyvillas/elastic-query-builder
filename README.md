# Build and execute ElasticSearch queries using a fluent PHP API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/elasticsearch-query-builder.svg?style=flat-square)](https://packagist.org/packages/seyvillas/elastic-query-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/seyvillas/elastic-query-builder.svg?style=flat-square)](https://packagist.org/packages/seyvillas/elastic-query-builder)

---

This package is a _lightweight_ query builder for ElasticSearch. It's based on the `spatie\elasticsearch-query-builder` package.

```php
use SeyVillas\ElasticQueryBuilder\Aggregations\MaxAggregation;
use SeyVillas\ElasticQueryBuilder\Builder;
use SeyVillas\ElasticQueryBuilder\Queries\MatchQuery;

$client = Elastic\Elasticsearch\ClientBuilder::create()->build();

$companies = (new Builder($client))
    ->index('companies')
    ->addQuery(MatchQuery::create('name', 'spatie', fuzziness: 3))
    ->addAggregation(MaxAggregation::create('score'))
    ->search();
```

## Installation

You can install the package via composer:

```bash
composer require seyvillas/elastic-query-builder
```

> **Note**
> If you're using `elasticsearch/elasticsearch` v7 you need to use [v1](https://github.com/spatie/elasticsearch-query-builder/tree/v1) of this package.

## Basic usage

The only class you really need to interact with is the `SeyVillas\ElasticQueryBuilder\Builder` class. It requires an `\Elastic\Elasticsearch\Client` passed in the constructor. Take a look at the [ElasticSearch SDK docs](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/installation.html) to learn more about connecting to your ElasticSearch cluster. 

The `Builder` class contains some methods to [add queries](#adding-queries), [aggregations](#adding-aggregations), [sorts](#adding-sorts), [fields](#retrieve-specific-fields) and some extras for [pagination](#pagination). You can read more about these methods below. Once you've fully built-up the query you can use `$builder->search()` to execute the query or `$builder->getPayload()` to get the raw payload for ElasticSearch.

```php
use SeyVillas\ElasticQueryBuilder\Queries\RangeQuery;
use SeyVillas\ElasticQueryBuilder\Builder;

$client = Elastic\Elasticsearch\ClientBuilder::create()->build();

$builder = new Builder($client);

$builder->addQuery(RangeQuery::create('age')->gte(18));

$results = $builder->search(); // raw response from ElasticSearch
```

## Adding queries

The `$builder->addQuery()` method can be used to add any of the available `Query` types to the builder. The available query types can be found below or in the `src/Queries` directory of this repo. Every `Query` has a static `create()` method to pass its most important parameters.

The following query types are available:

#### `ExistsQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\ExistsQuery::create('terms_and_conditions');
```

#### `MatchQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\MatchQuery::create('name', 'john doe', fuzziness: 2);
```

#### `MultiMatchQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\MultiMatchQuery::create('john', ['email', 'email'], fuzziness: 'auto');
```

#### `NestedQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\NestedQuery::create(
    'user', 
    new \SeyVillas\ElasticQueryBuilder\Queries\MatchQuery('name', 'john')
);
```

#### `RangeQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\RangeQuery::create('age')
    ->gte(18)
    ->lte(1337);
```

#### `TermQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\TermQuery::create('user.id', 'flx');
```

#### `WildcardQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html](https://www. elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\WildcardQuery::create('user.id', '*doe');
```

#### `GeoQuery`
```php
use SeyVillas\ElasticQueryBuilder\Geo\Point;

$builder
    ->addQuery(
        $distanceQuery = GeoDistanceQuery::create('location', Point::create($point->latitude, $point->longitude)),
    'filter'
);
```

#### `BoolQuery`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)

```php
\SeyVillas\ElasticQueryBuilder\Queries\BoolQuery::create()
    ->add($matchQuery, 'must_not')
    ->add($existsQuery, 'must_not');
```

### Chaining multiple queries

Multiple `addQuery()` calls can be chained on one `Builder`. Under the hood they'll be added to a `BoolQuery` with occurrence type `must`. By passing a second argument to the `addQuery()` method you can select a different occurrence type:

```php
$builder
    ->addQuery(
        MatchQuery::create('name', 'billie'), 
        'must_not' // available types: must, must_not, should, filter
    )
    ->addQuery(
        MatchQuery::create('team', 'eillish')
    );
```

More information on the boolean query and its occurrence types can be found [in the ElasticSearch docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html).

## Adding aggregations

The `$builder->addAggregation()` method can be used to add any of the available `Aggregation`s to the builder. The available aggregation types can be found below or in the `src/Aggregations` directory of this repo. Every `Aggregation` has a static `create()` method to pass its most important parameters and sometimes some extra methods.

```php
use SeyVillas\ElasticQueryBuilder\Aggregations\TermsAggregation;
use SeyVillas\ElasticQueryBuilder\Builder;

$results = (new Builder(Elastic\Elasticsearch\ClientBuilder::create()->build()))
    ->addAggregation(TermsAggregation::create('genres', 'genre'))
    ->search();

$genres = $results['aggregations']['genres']['buckets'];
```

The following query types are available:

#### `CardinalityAggregation`

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\CardinalityAggregation::create('team_agg', 'team_name');
```

#### `FilterAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\FilterAggregation::create(
    'tshirts',
    \SeyVillas\ElasticQueryBuilder\Queries\TermQuery::create('type', 'tshirt'),
    \SeyVillas\ElasticQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'price')
);
```

#### `MaxAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-max-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-max-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'price');
```

#### `MinAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-min-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-min-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\MinAggregation::create('min_price', 'price');
```

#### `AvgAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\AvgAggregation::create('avg_price', 'price');
```

#### `SumAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-sum-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-sum-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\SumAggregation::create('sum_price', 'price');
```

#### `NestedAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\NestedAggregation::create(
    'resellers',
    'resellers',
    \SeyVillas\ElasticQueryBuilder\Aggregations\MinAggregation::create('min_price', 'resellers.price'),
    \SeyVillas\ElasticQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'resellers.price'),
);
```

#### `ReverseNestedAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\ReverseNestedAggregation::create(
    'name',
    ...$aggregations
);
```

#### `TermsAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\TermsAggregation::create(
    'genres',
    'genre'
)
    ->size(10)
    ->order(['_count' => 'asc'])
    ->missing('N/A')
    ->aggregation(/* $subAggregation */);
```

#### `TopHitsAggregation`

[https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html)

```php
\SeyVillas\ElasticQueryBuilder\Aggregations\TopHitsAggregation::create(
    'top_sales_hits',
    size: 10,
);
```

## Adding sorts

The `Builder` (and some aggregations) has a `addSort()` method that takes a `ISort` instance to sort the results. You can read more about how sorting works in [the ElasticSearch docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/sort-search-results.html).

### `Standard Sort`
```php
use SeyVillas\ElasticQueryBuilder\Sorts\Sort;

$builder
    ->addSort(Sort::create('age', Sort::DESC))
    ->addSort(
        Sort::create('score', Sort::ASC)
            ->unmappedType('long')
            ->missing(0)
    );
```

### `GeoSort`

```php
use SeyVillas\ElasticQueryBuilder\Geo\Point;
use SeyVillas\ElasticQueryBuilder\Sorts\GeoSort;

$builder
    ->addSort(
        GeoSort::create('location', Point::create($point->latitude, $point->longitude))
    );
```

### `Random`
There's also possibility to use **Random** sort:

```php
use SeyVillas\ElasticQueryBuilder\Sorts\Random;

$builder
    ->addSort(Random::create());
```

## Retrieve specific fields

The `fields()` method can be used to request specific fields from the resulting documents without returning the entire `_source` entry. You can read more about the specifics of the fields parameter in [the ElasticSearch docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-fields.html).

```php
$builder->fields('user.id', 'http.*.status');
```

## Specify excludes/includes in the `_source`
The `addExclude($name)` or `addInclude($name)` methods can be used for specifying excludes or includes on the `_source`. More in [the ElasticSearch docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-source-field.html#include-exclude).

```php
$builder->addExclude('document key');
```
or you can use also `*`:
```php
$builder->addExclude('translations.*');
```

## Pagination

Finally the `Builder` also features a `size()` and `from()` method for the corresponding ElasticSearch search parameters. These can be used to build a paginated search. Take a look the following example to get a rough idea:

```php
use SeyVillas\ElasticQueryBuilder\Builder;

$pageSize = 100;
$pageNumber = $_GET['page'] ?? 1;

$pageResults = (new Builder(Elastic\Elasticsearch\ClientBuilder::create()))
    ->size($pageSize)
    ->from(($pageNumber - 1) * $pageSize)
    ->search();
```

## Scripts

It's also possible to add scripts as an array to the query. For example, here's a simple script that retrieves the first item from the images array and returns it in the $response['fields'] array: 
```php
$builder
    ->addScript(
        'images',
        [
            'source' => "params['_source']['images'][0]",
            'lang' => 'painless'
        ]
    );
```

This script checks if the images array exists and contains at least one item. If both conditions are met, it returns the first item from the images array; otherwise, it returns null.
```php
$builder
->addScript(
    'images',
    [
        'source' => "
            if (params['_source']['images'] != null && !params['_source']['images'].isEmpty()) {
                return params['_source']['images'][0];
            } else {
                return null;
            }
        ",
        'lang' => 'painless'
    ]
);

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits
- [Miroslav Koula](https://github.com/elcheco)
- [Alex Vanderbist](https://github.com/alexvanderbist)
- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
