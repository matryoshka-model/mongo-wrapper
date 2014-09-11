<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Paginator;

use Zend\Paginator\Adapter\AdapterInterface;
use Matryoshka\Model\ResultSet\HydratingResultSet;
use MongoCursor;

class MongoPaginatorAdapter implements AdapterInterface
{

    /**
     * @var MongoCursor
     */
    protected $cursor;

    /**
     * @var HydratingResultSet
     */
    protected $resultSetPrototype;

    /**
     * @var int
     */
    protected $count;

    /**
     * @param MongoCursor $cursor
     * @param HydratingResultSet $resultSetPrototype
     */
    public function __construct(MongoCursor $cursor, HydratingResultSet $resultSetPrototype = null)
    {
        $this->cursor    = $cursor;
        $this->resultSetPrototype = $resultSetPrototype ? $resultSetPrototype : new HydratingResultSet();

        $this->cursor->limit(null)->skip(null);
        $this->count     = $this->cursor->count();
    }

    /**
     * Returns the total number of results for this query
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Returns an result set of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return HydratingResultSet
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->cursor->skip($offset);
        $this->cursor->limit($itemCountPerPage);

        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($this->cursor);

        return $resultSet;
    }

}
