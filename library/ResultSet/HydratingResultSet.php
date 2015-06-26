<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\ResultSet;

use Matryoshka\Model\ResultSet\HydratingResultSet as BaseResultSet;
use MongoCursor;

/**
 * Class HydratingResultSet
 */
class HydratingResultSet extends BaseResultSet
{
    /**
     * @{inheritDoc}
     * @see http://php.net/manual/en/mongocursor.count.php
     */
    public function count()
    {
        if ($this->count === null && $this->dataSource instanceof MongoCursor) {
            $this->count = $this->dataSource->count(true);
        }
        return parent::count();
    }
}
