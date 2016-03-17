<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy;

use DateTime;
use MongoDate;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyInterface;
use Matryoshka\Model\Hydrator\Strategy\NullableStrategyTrait;
use Matryoshka\Model\Exception;

/**
 * Class MongoDateStrategy
 */
class MongoDateStrategy implements StrategyInterface, NullableStrategyInterface
{
    use NullableStrategyTrait;
    
    /**
     * @var string
     */
    protected $format;

    /**
     * Ctor
     * @param null|string $format
     */
    public function __construct($format = null)
    {
        $this->setFormat(DateTime::ISO8601);
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    
    /**
     * Convert a MongoDate to a DateTime
     * 
     * @param mixed $value
     * @return mixed|MongoDate
     */
    public function hydrate($value)
    {
        if ($value instanceof MongoDate) {
            return new DateTime(date($this->getFormat(), $value->sec));
        } 
        
        if ($this->nullable && $value === null) {
            return null;
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be an instance of MongoDate, "%s" given.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
    
    
    /**
     * Convert a DateTime to a MongoDate
     * 
     * @param mixed $value
     * @return DateTime|mixed
     */
    public function extract($value)
    {
        if ($value instanceof DateTime) {
            return new MongoDate($value->format('U'));
        }
        
        if ($this->nullable && $value === null) {
            return null;
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid value: must be an instance of DateTime, "%s" given.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }


    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
