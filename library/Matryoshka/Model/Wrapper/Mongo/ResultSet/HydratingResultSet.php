<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\ResultSet;

use MongoCursor;
use Matryoshka\Model\ResultSet\HydratingResultSet as BaseResultSet;

/**
 * Class HydratingResultSet
 */
class HydratingResultSet extends BaseResultSet
{
    /**
     * @{inheritDoc}
     */
    public function count()
    {
        if ($this->count === null && $this->dataSource instanceof MongoCursor) {
            /**
             * @see http://php.net/manual/en/mongocursor.count.php
             */
            $this->count = $this->dataSource->count(true);
        }
        return parent::count();
    }
}
