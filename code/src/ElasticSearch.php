<?php

namespace Pasha234\Hw10;

use Elastic\Elasticsearch\Client;
use Pasha234\Hw10\Models\ElasticModelInterface;

class ElasticSearch
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param class-string<ElasticModelInterface> $class
     * @param array $criteria
     * @param array $fitlers
     * 
     * @return array<int, ElasticModelInterface>
     */
    public function search(string $class, array $criteria = [], array $filters = []): array
    {
        $index = (new $class)->getIndex();
        $must = [];
        foreach ($criteria as $field => $value) {
            $must[] = ['match' => [$field => $value]];
        }

        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $must,
                        'filter' => $filters,
                    ]
                ]
            ]
        ];

        return array_map(function($item) use ($class) {
            return $class::createFromElastic($item);
        }, $this->getHits($this->client->search($params)->asArray()));
    }

    protected function getHits(array $response): array
    {
        return $response['hits']['hits'] ?? [];
    }

    public function aggregationSearch(string $class, string $aggregationKey, array $aggregationOptions): array
    {
        $index = (new $class)->getIndex();

        $params = [
            'index' => $index,
            'body' => [
                'size' => 0,
                'aggs' => [
                    $aggregationKey => $aggregationOptions
                ]
            ]
        ];

        $response = $this->client->search($params);

        return array_map(
            fn($bucket) => $bucket['key'],
            $response['aggregations'][$aggregationKey]['buckets'] ?? []
        );
    }
}
