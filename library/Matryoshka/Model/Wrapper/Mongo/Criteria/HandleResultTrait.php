<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Criteria;

use Matryoshka\Model\Wrapper\Mongo\Criteria\Exception\MongoResultException;
/**
 * Class HandleResultTrait
 */
trait HandleResultTrait
{
    /**
     * @param $result
     * @return int|null
     */
    protected function handleResult($result, $isInsert = false)
    {
        //No info available
        if ($result === true) {
            return null;
        }

        if (is_array($result)) {
            if (isset($result['ok']) && $result['ok']) { //This should almost always be 1 (unless last_error itself failed)
                if ($isInsert) {
                    return 1; //Mongo returns 0 on insert operation
                } else {
                    return isset($result['n']) ? (int) $result['n'] : null;
                }
            }

            if (isset($result['err']) && $result['err'] !== null) {
                throw new MongoResultException($result['errmsg'], $result['code']);
            }
        }

        return null;
    }
}
