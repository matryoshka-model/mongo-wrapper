<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy;

use MongoBinData;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyTrait;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyInterface;
use Matryoshka\Model\Exception;

/**
 * Class MongoBinDataStrategy
 */
class MongoBinDataStrategy implements StrategyInterface, NullableStrategyInterface
{
    use NullableStrategyTrait;
    
    /**
     * @var int
     */
    protected $type;

    /**
     * @param int $type
     */
    public function __construct($type = null)
    {
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = (int) $type;
        return $this;
    }
    
    /**
     * Convert a MongoBinData to binary string
     * 
     * @param mixed $value The original value.
     * @return null|string Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        if ($this->nullable && $value === null) {
            return null;
        }
        
        if ($value instanceof MongoBinData) {
            return $value->bin;
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be an instance of MongoBinData, "%s" given',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    /**
     * Ensure the value extracted is typed as MongoBinData or null
     *
     * @param mixed $value The original value.
     * @return null|\MongoBinData Returns the value that should be extracted.
     */
    public function extract($value)
    {
        if ($this->nullable && $value === null) {
            return null;
        }
        
        return new MongoBinData($value, $this->type);
    }
}
