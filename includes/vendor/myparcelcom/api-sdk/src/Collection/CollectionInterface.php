<?php

namespace MyParcelCom\ApiSdk\Collection;

use Iterator;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;

interface CollectionInterface extends Iterator
{
    /**
     * Counts the amount of resources in the collection.
     *
     * @return int
     */
    public function count();

    /**
     * Retrieves the resources based on limit and offset.
     * Max (and default) limit is 30.
     *
     * @return ResourceInterface[]
     */
    public function get();

    /**
     * Sets an offset on which resource to start retrieving.
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset);

    /**
     * Sets the amount of resources to be retrieved by get().
     * Default limit is 30, max limit is 100.
     *
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 30);
}
