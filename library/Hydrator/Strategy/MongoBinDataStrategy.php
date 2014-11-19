<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoBinDataStrategy
 */
class MongoBinDataStrategy implements StrategyInterface
{

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
     * Ensure the value extracted is typed as \MongoBinData or null
     * @param mixed $value The original value.
     * @return null|\MongoBinData Returns the value that should be extracted.
     */
    public function extract($value)
    {
        return null === $value ? null : new \MongoBinData($value);
    }

    /**
     * Ensure the value extracted is typed as string or null
     * @param mixed $value The original value.
     * @return null|string Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        return $value === null ? null : (string)$value;
    }
}
