<?php

namespace NguyenHuy\Menu\Database\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

class CacheableQueryBuilder extends Builder
{
    /**
     * The model class to cache against.
     */
    protected $modelClass;

    /**
     * The number of minutes to cache the query.
     */
    protected $minutes = 60;

    /**
     * The cache key for the query.
     */
    protected $enabled = false;

    /**
     * Prefix for the cache key.
     */
    protected $prefix = 'menu';

    /**
     * Create a new query builder instance.
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param \Illuminate\Database\Query\Grammars\Grammar|null $grammar
     * @param \Illuminate\Database\Query\Processors\Processor|null $processor
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null,
        string $modelClass = null
    ) {
        parent::__construct($connection, $grammar, $processor);
        $this->modelClass = $modelClass ?? static::class;

        // Load configuration values from the menu configuration file
        $this->minutes = config('menu.cache_ttl', $this->minutes);
        $this->enabled = config('menu.cache_enabled', $this->enabled);
        $this->prefix = config('menu.cache_key_prefix', $this->prefix);
    }


    /**
     * Pass our configuration to newly created queries
     *
     * @return $this|CacheableQueryBuilder
     */
    public function newQuery()
    {
        return new static(
            $this->connection,
            $this->grammar,
            $this->processor,
            $this->modelClass
        );
    }


    /**
     * Run the query as a "select" statement against the connection.
     *
     * Check the cache based on the query beforehand and return
     * a cached value or cache it if not already.
     *
     * @return array
     */
    protected function runSelect()
    {
        if (!$this->enabled) {
            return parent::runSelect();
        }

        // Use the query as the cache key
        $cacheKey = $this->getCacheKey();

        // Check if the cache store supports tags
        $isTaggableStore = Cache::getStore() instanceof TaggableStore;
        // Create additional identifiers based on the model class
        $modelClasses = $this->getIdentifiableModelClasses($this->getIdentifiableValue());

        // If the query is already cached, return the cached value
        if (($isTaggableStore && Cache::tags($modelClasses)->has($cacheKey)) || Cache::has($cacheKey)) {
            return $isTaggableStore ? Cache::tags($modelClasses)->get($cacheKey) : Cache::get($cacheKey);
        }

        // If not cached, run the query and cache the result
        $retVal = parent::runSelect();

        // If the cache store supports tags, cache the result with tags
        if ($isTaggableStore) {
            Cache::tags($modelClasses)->put($cacheKey, $retVal, $this->minutes);
        } else {
            // If not, cache the result and store the query for purging purposes
            foreach ($modelClasses as $modelClass) {
                $modelCacheKey = $this->getModelCacheKey($modelClass);
                $queries = Cache::get($modelCacheKey, []);
                $queries[] = $cacheKey;
                Cache::put($modelCacheKey, $queries);
            }
            Cache::put($cacheKey, $retVal, $this->minutes);
        }

        return $retVal;
    }

    /**
     * Check if to cache against just the class or a specific identifiable e.g. id
     *
     * @return string[]
     */
    protected function getIdentifiableModelClasses($value = null): array
    {
        $retVals = [$this->modelClass];
        if ($value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $retVals[] = "{$this->modelClass}#{$v}";
                }
            } else {
                $retVals[] = "{$this->modelClass}#{$value}";
            }
        }

        return $retVals;
    }

    /**
     * Get the identifiable value from the query's where clauses
     *
     * @param array|null $wheres
     * @return mixed|null
     */
    protected function getIdentifiableValue(array $wheres = null)
    {
        $wheres = $wheres ?? $this->wheres;
        foreach ($wheres as $where) {
            if (isset($where['type']) && $where['type'] === 'Nested') {
                return $this->getIdentifiableValue($where['query']->wheres);
            }
            if (isset($where['column']) && $where['column'] === 'id') {
                return $where['value'] ?? $where['values'];
            }
        }

        return null;
    }

    /**
     * Check if the query is an identifier query (id-driven)
     *
     * @param array|null $wheres
     * @return bool
     */
    protected function isIdentifiableQuery(array $wheres = null): bool
    {
        return $this->getIdentifiableValue($wheres) !== null;
    }

    /**
     * Purge all model queries and results from the cache
     *
     * @param null $identifier
     * @return bool
     */
    public function flushCache($identifier = null): bool
    {
        if (!$this->enabled) {
            return false;
        }
        // If the cache store supports tags, flush all results with the specified model classes
        $modelClasses = $this->getIdentifiableModelClasses($identifier);
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags($modelClasses)->flush();
        } else {
            // If not, forget the cached queries and results based on the model classes
            foreach ($modelClasses as $modelClass) {
                $modelCacheKey = $this->getModelCacheKey($modelClass);
                $queries = Cache::get($modelCacheKey);
                if (!empty($queries)) {
                    foreach ($queries as $query) {
                        Cache::forget($query);
                    }

                    Cache::forget($modelCacheKey);
                }
            }
        }

        return true;
    }

    /**
     * Build a cache key based on the SQL statement and its bindings
     *
     * @return string
     */
    protected function getCacheKey(): string
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        if (!empty($bindings)) {
            $bindings = implode('_', $this->getBindings());

            return $sql . '_' . $bindings;
        }

        return $sql;
    }

    /**
     * Get the cache key for the specified model class
     *
     * @param string|null $modelClass
     * @return string
     */
    protected function getModelCacheKey(string $modelClass = null): string
    {
        return $this->prefix . '_' . ($modelClass ?? $this->modelClass);
    }

    /**
     * Update records in the database and flush the cache
     *
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        $this->flushCache();

        return parent::update($values);
    }

    /**
     * Update records in the database from another query and flush the cache
     *
     * @param array $values
     * @return int
     */
    public function updateFrom(array $values)
    {
        $this->flushCache();

        return parent::updateFrom($values);
    }

    /**
     * Insert records into the database and flush the cache
     *
     * @param array $values
     * @return bool
     */
    public function insert(array $values)
    {
        $this->flushCache();

        return parent::insert($values);
    }

    /**
     * Insert records into the database and return the last inserted ID, flushing the cache
     *
     * @param array $values
     * @param       $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $this->flushCache();

        return parent::insertGetId($values, $sequence);
    }

    /**
     * Insert records into the database, ignoring duplicates, and flush the cache
     *
     * @param array $values
     * @return int
     */
    public function insertOrIgnore(array $values)
    {
        $this->flushCache();

        return parent::insertOrIgnore($values);
    }

    /**
     * Insert records into the database using a subquery and flush the cache
     *
     * @param array $columns
     * @param       $query
     * @return int
     */
    public function insertUsing(array $columns, $query)
    {
        $this->flushCache();

        return parent::insertUsing($columns, $query);
    }

    /**
     * Upsert records in the database and flush the cache
     *
     * @param array $values
     * @param       $uniqueBy
     * @param       $update
     * @return int
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->flushCache();

        return parent::upsert($values, $uniqueBy, $update);
    }

    /**
     * Delete records from the database and flush the cache
     *
     * @param $id
     * @return int
     */
    public function delete($id = null)
    {
        $this->flushCache($id);

        return parent::delete($id);
    }

    /**
     * Truncate the table and flush the cache
     *
     * @return void
     */
    public function truncate()
    {
        $this->flushCache();

        parent::truncate();
    }
}
