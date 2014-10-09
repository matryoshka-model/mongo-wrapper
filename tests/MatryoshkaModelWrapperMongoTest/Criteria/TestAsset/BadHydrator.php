<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class BadHydrator
 */
class BadHydrator implements HydratorInterface
{
    /**
     * {@inheritdocs}
     */
    public function extract($object)
    {
        // Do nothing
    }

    /**
     * {@inheritdocs}
     */
    public function hydrate(array $data, $object)
    {
        // Do nothing
    }
}
