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
use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupParameterInterface;
use KmbDomain\Model\GroupParameterRepositoryInterface;
use KmbDomain\Model\GroupClassInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class GroupParameterRepository extends Repository implements GroupParameterRepositoryInterface
{
    /** @var string */
    protected $groupValueTableName;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        /** @var GroupParameterInterface $aggregateRoot */
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
                $insert = $this->getMasterSql()->insert($this->groupValueTableName)->values([
                    'group_parameter_id' => $aggregateRoot->getId(),
                    'value' => $value,
                ]);
                $this->performWrite($insert);
            }
        }
        return $this;
    }

    public function update(AggregateRootInterface $aggregateRoot)
    {
        /** @var GroupParameterInterface $aggregateRoot */
        parent::update($aggregateRoot);

        $delete = $this->getMasterSql()->delete($this->groupValueTableName);
        $delete->where(['group_parameter_id' => $aggregateRoot->getId()]);
        $this->performWrite($delete);

        if ($aggregateRoot->hasValues()) {
            foreach ($aggregateRoot->getValues() as $value) {
                $insert = $this->getMasterSql()->insert($this->groupValueTableName)->values([
                    'group_parameter_id' => $aggregateRoot->getId(),
                    'value' => $value,
                ]);
                $this->performWrite($insert);
            }
        }
        return $this;
    }

    /**
     * @param GroupClassInterface $class
     * @return GroupParameterInterface[]
     */
    public function getAllByClass($class)
    {
        $criteria = new Where();
        $criteria
            ->equalTo('group_class_id', $class->getId())
            ->and
            ->isNull('parent_id');
        return $this->getAllBy($criteria);
    }

    /**
     * @param GroupParameterInterface $parent
     * @return GroupParameterInterface[]
     */
    public function getAllByParent($parent)
    {
        $criteria = new Where();
        $criteria->equalTo('parent_id', $parent->getId());
        return $this->getAllBy($criteria);
    }

    /**
     * @param GroupParameterInterface $child
     * @return GroupParameterInterface
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
            ['v' => $this->getGroupValueTableName()],
            $this->getTableName() . '.id = v.group_parameter_id',
            [
                'value' => 'value',
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
            $groupParameterId = $row['id'];
            /** @var GroupParameter $aggregateRoot */
            if (!array_key_exists($groupParameterId, $aggregateRoots)) {
                $aggregateRoot = new $aggregateRootClassName;
                $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
                $aggregateRoots[$groupParameterId] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
            } else {
                $aggregateRoot = $aggregateRoots[$groupParameterId];
            }

            if (isset($row['value'])) {
                $aggregateRoot->addValue($row['value']);
            }
        }
        return array_values($aggregateRoots);
    }

    /**
     * Set GroupValueTableName.
     *
     * @param string $groupValueTableName
     * @return GroupParameterRepository
     */
    public function setGroupValueTableName($groupValueTableName)
    {
        $this->groupValueTableName = $groupValueTableName;
        return $this;
    }

    /**
     * Get ValueTableName.
     *
     * @return string
     */
    public function getGroupValueTableName()
    {
        return $this->groupValueTableName;
    }
}
