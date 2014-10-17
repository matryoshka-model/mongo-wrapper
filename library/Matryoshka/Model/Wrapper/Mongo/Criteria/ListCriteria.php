<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria;


use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Criteria\AbstractCriteria;

/**
 * Class ActiveRecordCriteria
 */
class ListCriteria extends AbstractCriteria
{
    use HandleResultTrait;


    /**
     * Mongo selection criteria
     *
     * By default this criteria selects all document in a collection.
     * Extended classes can specify selection criteria using query operators
     * in order to restrict the selection.
     *
     * @see http://docs.mongodb.org/manual/reference/method/db.collection.find/
     *
     * @var array
     */
    protected $selectionCriteria = [];


    /**
     * Mongo sort params
     *
     * Extended class can specifies the order in which the query returns matching documents.
     *
     * @see http://docs.mongodb.org/manual/reference/method/cursor.sort/
     *
     * @var array
     */
    protected $sortParams = [];

    /**
     * @var array
     */
    protected $saveOptions = [];


    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function extractValue(ModelInterface $model, $name, $value, $object = null)
    {
        if (!$model->getHydrator() instanceof AbstractHydrator) {
            throw new Exception\RuntimeException(
                'Hydrator must be an instance of \Zend\Stdlib\Hydrator\AbstractHydrator'
            );
        }

        return $model->getHydrator()->extractValue($name, $value, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();

        $cursor = $dataGateway->find($this->selectionCriteria);
        if (!empty($this->sortParams)) {
            $cursor->sort($this->sortParams);
        }

        return $cursor->limit($this->limit)->skip($this->offset);
    }


    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        $justOne = $this->limit === 1 ? true : false;

        if (!$justOne && null !== $this->limit) {
            throw new Exception\InvalidArgumentException('Only no limit or just limit to one document are supported in write operations');
        }

        if (null !== $this->offset) {
            throw new Exception\InvalidArgumentException('Offset is not supported in write operations');
        }

        $options = array_merge(['upsert' => true], $this->getSaveOptions());
        $options['multi'] = !$justOne;

        $tmp = $data;  // passing a referenced variable to save will fail in update the content
        $result = $model->getDataGateway()->update($this->selectionCriteria, $tmp, $this->getSaveOptions());
        $data = $tmp;

        return $this->handleResult($result);
    }

    /**
     * @return array
     */
    public function getSaveOptions()
    {
        return $this->saveOptions;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setSaveOptions(array $options)
    {
        $this->saveOptions = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        $justOne = $this->limit === 1 ? true : false;

        if (!$justOne && null !== $this->limit) {
            throw new Exception\InvalidArgumentException('Only no limit or just limit to one document are supported in delete operations');
        }

        if (null !== $this->offset) {
            throw new Exception\InvalidArgumentException('Offset is not supported in delete operations');
        }

        $result = $model->getDataGateway()->remove($this->selectionCriteria, $justOne);
        return $this->handleResult($result, true);
    }
}
