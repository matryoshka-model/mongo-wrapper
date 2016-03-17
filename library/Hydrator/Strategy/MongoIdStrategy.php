<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy;

use MongoId;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyTrait;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyInterface;
use Matryoshka\Model\Exception;

/**
 * Class MongoIdStrategy
 */
class MongoIdStrategy implements StrategyInterface, NullableStrategyInterface
{
    use NullableStrategyTrait;
    
    /**
     * Ensure the value extracted is typed as string or null
     *
     * @param mixed $value The original value.
     * @return null|string Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        if ($value instanceof MongoId) {
            return (string)$value;
        }
        
        if ($this->nullable && $value === null) {
            return null;
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be an instance of MongoId, "%s" given.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
    
    
    /**
     * Ensure the value extracted is typed as MongoId or null
     *
     * @param mixed $value The original value.
     * @return null|MongoId Returns the value that should be extracted.
     */
    public function extract($value)
    {
        if (is_string($value)) {
            return new MongoId($value);
        }
        
        if ($this->nullable && $value === null) {
            return null;
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be a string containing a valid mongo ID, "%s" given.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
