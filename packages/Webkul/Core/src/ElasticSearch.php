<?php

namespace Webkul\Core;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Arr;

class ElasticSearch
{
    /**
     * Map configuration array keys with ES ClientBuilder setters
     *
     * @var array
     */
    protected $configMappings = [
        'retries' => 'setRetries',
        'caBundle' => 'setCABundle',
    ];

    /**
     * Make a new connection.
     */
    protected function makeConnection(?string $name = null): Client
    {
        $connection = $name ?: $this->getDefaultConnection();

        $config = $this->getConnectionConfig($connection);

        $clientBuilder = ClientBuilder::create();

        if ($connection == 'default') {
            /**
             * Build default connection.
             */
            $clientBuilder->setHosts($config['hosts'])
                ->setBasicAuthentication($config['user'] ?: '', $config['pass'] ?: '');
        } elseif ($connection == 'api') {
            /**
             * Build API key connection.
             */
            $clientBuilder->setHosts($config['hosts'])
                ->setApiKey($config['key']);
        } elseif ($connection == 'cloud') {
            /**
             * Build elastic cloud connection.
             */
            $clientBuilder->setElasticCloudId($config['id']);

            if ($config['api_key']) {
                $clientBuilder->setApiKey($config['api_key']);
            } else {
                $clientBuilder->setBasicAuthentication($config['user'], $config['pass']);
            }
        }

        /**
         * Set additional client configuration.
         */
        foreach ($this->configMappings as $key => $method) {
            $value = Arr::get(config('elasticsearch'), $key);

            if (! is_null($value)) {
                $clientBuilder->$method($value);
            }
        }

        return $clientBuilder->build();
    }

    /**
     * Get the default connection.
     */
    public function getDefaultConnection(): string
    {
        return config('elasticsearch.connection');
    }

    /**
     * Get the configuration for a named connection.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function getConnectionConfig(string $name)
    {
        $connections = config('elasticsearch.connections');

        if (null === $config = Arr::get($connections, $name)) {
            throw new \InvalidArgumentException("Elasticsearch connection [$name] not configured.");
        }

        return $config;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        try {
            return call_user_func_array([$this->makeConnection(), $method], $parameters);
        } catch (\Elastic\Elasticsearch\Exception\ClientResponseException $e) {
            $statusCode = null;
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
            } else {
                $statusCode = $e->getCode();
            }

            if ($statusCode === 404) {
                if ($method === 'search') {
                    $index = $parameters[0]['index'] ?? null;
                    if ($index) {
                        $this->handleMissingIndex($index);

                        // Retry the search query after index creation
                        try {
                            return call_user_func_array([$this->makeConnection(), $method], $parameters);
                        } catch (\Elastic\Elasticsearch\Exception\ClientResponseException $retryException) {
                            $retryStatusCode = null;
                            if (method_exists($retryException, 'getResponse') && $retryException->getResponse()) {
                                $retryStatusCode = $retryException->getResponse()->getStatusCode();
                            } else {
                                $retryStatusCode = $retryException->getCode();
                            }

                            if ($retryStatusCode === 404) {
                                return [
                                    'hits' => [
                                        'total' => [
                                            'value' => 0,
                                            'relation' => 'eq',
                                        ],
                                        'hits' => [],
                                    ],
                                    'suggest' => [
                                        'name_suggest' => [
                                            [
                                                'options' => [],
                                            ],
                                        ],
                                    ],
                                    'aggregations' => [
                                        'max_price' => ['value' => 0],
                                        'min_price' => ['value' => 0],
                                    ],
                                ];
                            }
                            throw $retryException;
                        }
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * Handle missing index by creating it and triggering a background reindexing.
     *
     * @param  string|array  $index
     * @return void
     */
    protected function handleMissingIndex($index): void
    {
        $indices = is_array($index) ? $index : [$index];
        $client = $this->makeConnection();

        foreach ($indices as $indexName) {
            try {
                $existsResponse = $client->indices()->exists(['index' => $indexName]);
                $exists = false;
                if (method_exists($existsResponse, 'asBool')) {
                    $exists = $existsResponse->asBool();
                } elseif (method_exists($existsResponse, 'getStatusCode')) {
                    $exists = $existsResponse->getStatusCode() === 200;
                } else {
                    $exists = (bool) $existsResponse;
                }

                if (! $exists) {
                    $client->indices()->create(['index' => $indexName]);

                    // Trigger background reindex
                    $lockKey = 'elasticsearch_reindexing_' . $indexName;
                    if (! \Illuminate\Support\Facades\Cache::has($lockKey)) {
                        \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addMinutes(15));

                        if (class_exists(\Webkul\Product\Jobs\ElasticSearch\ReindexFull::class)) {
                            \Webkul\Product\Jobs\ElasticSearch\ReindexFull::dispatch();
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ElasticSearch Auto-Indexing Error: ' . $e->getMessage());
            }
        }
    }
}

