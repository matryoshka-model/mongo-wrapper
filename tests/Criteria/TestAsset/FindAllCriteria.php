<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Matryoshka\Model\Wrapper\Mongo\Criteria\FindAllCriteria as BaseCriteria;

class FindAllCriteria extends BaseCriteria
{

    public function setSort($sort)
    {
        $this->sortParams = $sort;
    }

    public function setSelectionCriteria($selection)
    {
        $this->selectionCriteria = $selection;
    }

    public function getSelectionCriteria()
    {
        return $this->selectionCriteria;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }



}