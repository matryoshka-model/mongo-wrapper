<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Matryoshka\Model\Wrapper\Mongo\Criteria\FindAllCriteria as BaseCriteria;

/**
 * Class FindAllCriteria
 */
class FindAllCriteria extends BaseCriteria
{
    /**
     * @param $sort
     */
    public function setSort($sort)
    {
        $this->sortParams = $sort;
    }

    /**
     * @param $selection
     */
    public function setSelectionCriteria($selection)
    {
        $this->selectionCriteria = $selection;
    }

    /**
     * @return array
     */
    public function getSelectionCriteria()
    {
        return $this->selectionCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
