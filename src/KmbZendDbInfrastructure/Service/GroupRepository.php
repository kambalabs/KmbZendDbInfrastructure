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
use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use KmbDomain\Model\GroupClassInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\RevisionInterface;
use KmbZendDbInfrastructure\Proxy\GroupClassProxy;
use KmbZendDbInfrastructure\Proxy\GroupParameterProxy;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class GroupRepository extends Repository implements GroupRepositoryInterface
{
    /** @var  GroupClassRepository */
    protected $groupClassRepository;

    /** @var  GroupParameterRepository */
    protected $groupParameterRepository;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return GroupRepository
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        /** @var GroupInterface $aggregateRoot */
        $select = $this->getSlaveSql()
            ->select()
            ->from($this->getTableName())
            ->columns(['ordering' => new Expression('MAX(ordering)')])
            ->where(['revision_id' => $aggregateRoot->getRevision()->getId()]);
        $result = $this->performRead($select)->current();
        $ordering = $result['ordering'] === null ? 0 : $result['ordering'] + 1;
        $aggregateRoot->setOrdering($ordering);
        parent::add($aggregateRoot);

        if ($aggregateRoot->hasClasses()) {
            foreach ($aggregateRoot->getClasses() as $class) {
                $class->setGroup($aggregateRoot);
                $this->groupClassRepository->add($class);
            }
        }

        return $this;
    }

    /**
     * @param int[] ids
     * @return GroupInterface[]
     */
    public function getAllByIds(array $ids)
    {
        $criteria = new Where();
        $criteria
            ->isNull('p.parent_id')
            ->and
            ->in($this->getTableName() . '.id', $ids);
        return $this->getAllBy($criteria);
    }

    /**
     * @param RevisionInterface $revision
     * @return GroupInterface[]
     */
    public function getAllByRevision(RevisionInterface $revision)
    {
        $criteria = new Where();
        $criteria
            ->isNull('p.parent_id')
            ->and
            ->equalTo('revision_id', $revision->getId());
        return $this->getAllBy($criteria);
    }

    /**
     * @param string            $name
     * @param RevisionInterface $revision
     * @return GroupInterface
     */
    public function getByNameAndRevision($name, RevisionInterface $revision)
    {
        $criteria = new Where();
        $criteria
            ->isNull('p.parent_id')
            ->and
            ->equalTo($this->getTableName() . '.name', $name)
            ->and
            ->equalTo('revision_id', $revision->getId());
        return $this->getBy($criteria);
    }

    /**
     * @param GroupClassInterface $class
     * @return GroupInterface
     */
    public function getByClass(GroupClassInterface $class)
    {
        $criteria = new Where();
        $criteria
            ->isNull('p.parent_id')
            ->and
            ->equalTo('c.id', $class->getId());
        return $this->getBy($criteria);
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        $criteria = new Where();
        $criteria->isNull('p.parent_id');
        $select = parent::getSelect()
            ->join(
                ['c' => $this->groupClassRepository->getTableName()],
                $this->getTableName() . '.id = c.group_id',
                [
                    'c.id' => 'id',
                    'c.group_id' => 'group_id',
                    'c.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['p' => $this->groupParameterRepository->getTableName()],
                'c.id = p.group_class_id',
                [
                    'p.id' => 'id',
                    'p.group_class_id' => 'group_class_id',
                    'p.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['v' => $this->groupParameterRepository->getGroupValueTableName()],
                'p.id = v.group_parameter_id',
                [
                    'value' => 'value',
                ],
                Select::JOIN_LEFT
            )
            ->where($criteria)
            ->order($this->getTableName() . '.ordering, c.name, p.id, v.id');
        return $select;
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    protected function hydrateAggregateRootsFromResult(ResultInterface $result)
    {
        $aggregateRootClassName = $this->getAggregateRootClass();
        $groupClassClassName = $this->groupClassRepository->getAggregateRootClass();
        $groupParameterClassName = $this->groupParameterRepository->getAggregateRootClass();
        $aggregateRoots = [];
        foreach ($result as $row) {
            $groupId = $row['id'];
            /** @var GroupInterface $aggregateRoot */
            if (!array_key_exists($groupId, $aggregateRoots)) {
                $aggregateRoot = new $aggregateRootClassName;
                $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
                $aggregateRoots[$groupId] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
            } else {
                $aggregateRoot = $aggregateRoots[$groupId];
            }

            if (isset($row['c.name'])) {
                $groupClass = $aggregateRoot->getClassByName($row['c.name']);
                if ($groupClass === null) {
                    $groupClass = new $groupClassClassName;
                    $this->groupClassRepository->getAggregateRootHydrator()->hydrate($row, $groupClass);
                    /** @var GroupClassProxy $groupClassProxy */
                    $groupClassProxy = $this->groupClassRepository->getAggregateRootProxyFactory()->createProxy($groupClass);
                    $aggregateRoot->addClass($groupClassProxy);
                }
                if (isset($row['p.name'])) {
                    $groupParameter = $groupClass->getParameterByName($row['p.name']);
                    if ($groupParameter === null) {
                        $groupParameter = new $groupParameterClassName;
                        $this->groupParameterRepository->getAggregateRootHydrator()->hydrate($row, $groupParameter);
                        /** @var GroupParameterProxy $groupParameterProxy */
                        $groupParameterProxy = $this->groupParameterRepository->getAggregateRootProxyFactory()->createProxy($groupParameter);
                        $groupClass->addParameter($groupParameterProxy);
                    }
                    if (isset($row['value'])) {
                        $groupParameter->addValue($row['value']);
                    }
                }
            }
        }
        return array_values($aggregateRoots);
    }

    /**
     * Set GroupClassRepository.
     *
     * @param \KmbZendDbInfrastructure\Service\GroupClassRepository $groupClassRepository
     * @return GroupRepository
     */
    public function setGroupClassRepository($groupClassRepository)
    {
        $this->groupClassRepository = $groupClassRepository;
        return $this;
    }

    /**
     * Get GroupClassRepository.
     *
     * @return \KmbZendDbInfrastructure\Service\GroupClassRepository
     */
    public function getGroupClassRepository()
    {
        return $this->groupClassRepository;
    }

    /**
     * Set GroupParameterRepository.
     *
     * @param \KmbZendDbInfrastructure\Service\GroupParameterRepository $groupParameterRepository
     * @return GroupRepository
     */
    public function setGroupParameterRepository($groupParameterRepository)
    {
        $this->groupParameterRepository = $groupParameterRepository;
        return $this;
    }

    /**
     * Get GroupParameterRepository.
     *
     * @return \KmbZendDbInfrastructure\Service\GroupParameterRepository
     */
    public function getGroupParameterRepository()
    {
        return $this->groupParameterRepository;
    }
}
