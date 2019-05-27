<?php

namespace MyParcelCom\ApiSdk\Collection;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;

class ArrayCollection implements CollectionInterface
{
    /** @var ResourceInterface[] */
    protected $resources;

    /** @var int */
    protected $offset = 0;

    /** @var int */
    protected $limit = 30;

    /** @var int */
    protected $currentResourceNumber = 0;

    /**
     * ArrayCollection constructor.
     *
     * @param ResourceInterface[] $resources
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * Counts the amount of resources in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->resources);
    }

    /**
     * Retrieves the resources based on limit and offset.
     * Max (and default) limit is 30.
     *
     * @return ResourceInterface[]
     */
    public function get()
    {
        return array_filter($this->resources, function ($resourceNumber) {
            return $resourceNumber >= $this->offset && $resourceNumber < ($this->offset + $this->limit);
        }, ARRAY_FILTER_USE_KEY);
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
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
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
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->currentResourceNumber++;
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
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
}
