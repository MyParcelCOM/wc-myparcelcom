<?php

namespace MyParcelCom\ApiSdk\Collection;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;

class RequestCollection implements CollectionInterface
{
    /** @var callable */
    protected $promiseCreator;

    /** @var callable */
    protected $resourceCreator;

    /** @var ResourceInterface[] */
    protected $resources = [];

    /** @var int */
    protected $offset = 0;

    /** @var int */
    protected $limit = 30;

    /** @var int */
    protected $count;

    /** @var int */
    protected $currentResourceNumber = 0;

    /**
     * PromiseCollection constructor.
     *
     * @param callable $promiseCreator
     * @param callable $resourceCreator
     */
    public function __construct(callable $promiseCreator, callable $resourceCreator)
    {
        $this->promiseCreator = $promiseCreator;
        $this->resourceCreator = $resourceCreator;
    }

    /**
     * Counts the amount of resources in the collection.
     *
     * @return int
     */
    public function count()
    {
        if (!isset($this->count)) {
            $this->get();
        }

        return $this->count;
    }

    /**
     * Retrieves the resources based on limit and offset.
     * Max (and default) limit is 30.
     *
     * @return ResourceInterface[]
     */
    public function get()
    {
        if (isset($this->count) && $this->offset >= $this->count) {
            return [];
        }

        // Retrieving the resources from the api is done by page.
        // A maximum of 30 resources per page can be retrieved at once.
        // We need to specify which page should be retrieved from the api,
        // or which pages if the set offset and limit overlaps two pages.
        $firstPage = ceil(($this->offset + 1) / 30);
        $secondPage = ceil(($this->offset + $this->limit) / 30);

        if (!isset($this->resources[$this->offset])) {
            $this->retrieveResources($firstPage);
        }

        if (
            $firstPage !== $secondPage
            && !isset($this->resources[$this->offset + $this->limit - 1])
            && (($secondPage - 1) * 30) < $this->count
        ) {
            $this->retrieveResources($secondPage);
        }

        return array_filter($this->resources, function ($resourceNumber) {
            return $resourceNumber >= $this->offset && $resourceNumber < ($this->offset + $this->limit);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Retrieve the resources for the given page number and store them
     * in this->resources according to their number.
     *
     * @param int $pageNumber
     * @return void
     */
    private function retrieveResources($pageNumber)
    {
        $response = call_user_func_array($this->promiseCreator, [$pageNumber, 30]);

        $body = json_decode($response->getBody(), true);

        $this->count = $body['meta']['total_records'];
        $resources = call_user_func($this->resourceCreator, $body['data']);

        $resourceNumber = ($pageNumber - 1) * 30;

        array_walk($resources, function ($resource) use ($pageNumber, &$resourceNumber) {
            $this->resources[$resourceNumber] = $resource;
            $resourceNumber++;
        });
    }

    /**
     * Sets an offset on which resource to start retrieving.
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        $this->rewind();

        return $this;
    }

    /**
     * Sets the amount of resources to be retrieved by get().
     * Max and default limit is 30.
     *
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 30)
    {
        $this->limit = min(30, max(1, $limit));

        return $this;
    }

    /**
     * Return the current resource
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return ResourceInterface
     * @since 5.0.0
     */
    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->resources[$this->currentResourceNumber];
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return int|null int on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->currentResourceNumber;
    }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if (!isset($this->resources[$this->currentResourceNumber])) {
            $this->get();
        }

        return isset($this->resources[$this->currentResourceNumber])
            && $this->currentResourceNumber < ($this->offset + $this->limit);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->currentResourceNumber = $this->offset;
    }

    /**
     * Move forward to next resource
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->currentResourceNumber++;
    }
}
