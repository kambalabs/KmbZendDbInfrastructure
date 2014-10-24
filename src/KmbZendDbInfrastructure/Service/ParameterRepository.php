<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbZendDbInfrastructure\Service;

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistBase\Model\RepositoryInterface;
use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use KmbDomain\Model\Parameter;
use KmbDomain\Model\ParameterInterface;
use KmbDomain\Model\ParameterRepositoryInterface;
use KmbDomain\Model\PuppetClassInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ParameterRepository extends Repository implements ParameterRepositoryInterface
{
    /** @var string */
    protected $valueTableName;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        /** @var ParameterInterface $aggregateRoot */
        parent::add($aggregateRoot);

        if ($aggregateRoot->hasChildren()) {
            foreach ($aggregateRoot->getChildren() as $child) {
                $child->setParent($aggregateRoot);
                $child->setClass($aggregateRoot->getClass());
                $this->add($child);
            }
        }

        if ($aggregateRoot->hasValues()) {
            foreach ($aggregateRoot->getValues() as $value) {
                $insert = $this->getMasterSql()->insert($this->valueTableName)->values([
                    'parameter_id' => $aggregateRoot->getId(),
                    'name' => $value,
                ]);
                $this->performWrite($insert);
            }
        }
        return $this;
    }

    public function update(AggregateRootInterface $aggregateRoot)
    {
        /** @var ParameterInterface $aggregateRoot */
        parent::update($aggregateRoot);

        $delete = $this->getMasterSql()->delete($this->valueTableName);
        $delete->where(['parameter_id' => $aggregateRoot->getId()]);
        $this->performWrite($delete);

        if ($aggregateRoot->hasValues()) {
            foreach ($aggregateRoot->getValues() as $value) {
                $insert = $this->getMasterSql()->insert($this->valueTableName)->values([
                    'parameter_id' => $aggregateRoot->getId(),
                    'name' => $value,
                ]);
                $this->performWrite($insert);
            }
        }
        return $this;
    }

    /**
     * @param PuppetClassInterface $class
     * @return ParameterInterface[]
     */
    public function getAllByClass($class)
    {
        $criteria = new Where();
        $criteria
            ->equalTo('puppet_class_id', $class->getId())
            ->and
            ->isNull('parent_id');
        return $this->getAllBy($criteria);
    }

    /**
     * @param ParameterInterface $parent
     * @return ParameterInterface[]
     */
    public function getAllByParent($parent)
    {
        $criteria = new Where();
        $criteria->equalTo('parent_id', $parent->getId());
        return $this->getAllBy($criteria);
    }

    /**
     * @param ParameterInterface $child
     * @return ParameterInterface
     */
    public function getByChild($child)
    {
        $criteria = new Where();
        $criteria->equalTo('p.id', $child->getId());

        $select = $this->getSelect()
            ->join(
                ['p' => $this->getTableName()],
                $this->getTableName() . '.id = p.parent_id',
                []
            )
            ->where($criteria);

        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return parent::getSelect()->join(
            ['v' => $this->getValueTableName()],
            $this->getTableName() . '.id = v.parameter_id',
            [
                'value' => 'name',
            ],
            Select::JOIN_LEFT
        )->order($this->getTableName() . '.id, v.id');
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    protected function hydrateAggregateRootsFromResult(ResultInterface $result)
    {
        $aggregateRootClassName = $this->getAggregateRootClass();
        $aggregateRoots = [];
        foreach ($result as $row) {
            $parameterId = $row['id'];
            /** @var Parameter $aggregateRoot */
            if (!array_key_exists($parameterId, $aggregateRoots)) {
                $aggregateRoot = new $aggregateRootClassName;
                $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
                $aggregateRoots[$parameterId] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
            } else {
                $aggregateRoot = $aggregateRoots[$parameterId];
            }

            if (isset($row['value'])) {
                $aggregateRoot->addValue($row['value']);
            }
        }
        return array_values($aggregateRoots);
    }

    /**
     * Set ValueTableName.
     *
     * @param string $valueTableName
     * @return ParameterRepository
     */
    public function setValueTableName($valueTableName)
    {
        $this->valueTableName = $valueTableName;
        return $this;
    }

    /**
     * Get ValueTableName.
     *
     * @return string
     */
    public function getValueTableName()
    {
        return $this->valueTableName;
    }
}
