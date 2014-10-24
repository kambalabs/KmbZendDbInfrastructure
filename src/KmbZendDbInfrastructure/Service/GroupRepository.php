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
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use KmbDomain\Model\Group;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\GroupClassInterface;
use KmbDomain\Model\RevisionInterface;
use KmbZendDbInfrastructure\Proxy\EnvironmentProxy;
use KmbZendDbInfrastructure\Proxy\GroupParameterProxy;
use KmbZendDbInfrastructure\Proxy\GroupClassProxy;
use KmbZendDbInfrastructure\Proxy\RevisionProxy;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Stdlib\Hydrator\HydratorInterface;

class GroupRepository extends Repository implements GroupRepositoryInterface
{
    /** @var string */
    protected $revisionClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $revisionProxyFactory;

    /** @var HydratorInterface */
    protected $revisionHydrator;

    /** @var string */
    protected $revisionTableName;

    /** @var string */
    protected $environmentClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $environmentProxyFactory;

    /** @var HydratorInterface */
    protected $environmentHydrator;

    /** @var string */
    protected $environmentTableName;

    /** @var string */
    protected $groupClassClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $groupClassProxyFactory;

    /** @var HydratorInterface */
    protected $groupClassHydrator;

    /** @var string */
    protected $groupClassTableName;

    /** @var string */
    protected $groupParameterClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $groupParameterProxyFactory;

    /** @var HydratorInterface */
    protected $groupParameterHydrator;

    /** @var string */
    protected $groupParameterTableName;

    /** @var string */
    protected $groupValueTableName;

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
        return parent::add($aggregateRoot);
    }

    /**
     * @param int[] ids
     * @return GroupInterface[]
     */
    public function getAllByIds(array $ids)
    {
        $criteria = new Where();
        $criteria->in($this->getTableName() . '.id', $ids);
        return $this->getAllBy($criteria);
    }

    /**
     * @param RevisionInterface $revision
     * @return GroupInterface[]
     */
    public function getAllByRevision(RevisionInterface $revision)
    {
        $criteria = new Where();
        $criteria->equalTo('revision_id', $revision->getId());
        return $this->getAllBy($criteria);
    }

    /**
     * @param RevisionInterface $revision
     * @return GroupInterface
     */
    public function getFirstByRevision(RevisionInterface $revision)
    {
        $criteria = new Where();
        $criteria->equalTo($this->getTableName() . '.revision_id', $revision->getId());
        $select = $this->getSelect()->where($criteria)->limit(1);
        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * @param string            $name
     * @param RevisionInterface $revision
     * @return GroupInterface
     */
    public function getByNameAndRevision($name, RevisionInterface $revision)
    {
        $criteria = new Where();
        $criteria->equalTo($this->getTableName() . '.name', $name)
            ->and->equalTo($this->getTableName() . '.revision_id', $revision->getId());
        return $this->getBy($criteria);
    }

    /**
     * @param GroupClassInterface $class
     * @return GroupInterface
     */
    public function getByClass(GroupClassInterface $class)
    {
        $criteria = new Where();
        $criteria->equalTo('c.id', $class->getId());
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
                ['r' => $this->getRevisionTableName()],
                $this->getTableName() . '.revision_id = r.id',
                [
                    'r.id' => 'id',
                    'r.environment_id' => 'environment_id',
                    'r.updated_at' => 'updated_at',
                    'r.updated_by' => 'updated_by',
                    'r.released_at' => 'released_at',
                    'r.released_by' => 'released_by',
                    'r.comment' => 'comment',
                ]
            )
            ->join(
                ['e' => $this->getEnvironmentTableName()],
                'r.environment_id = e.id',
                [
                    'e.id' => 'id',
                    'e.name' => 'name',
                    'e.isdefault' => 'isdefault',
                ]
            )
            ->join(
                ['c' => $this->getGroupClassTableName()],
                $this->getTableName() . '.id = c.group_id',
                [
                    'c.id' => 'id',
                    'c.group_id' => 'group_id',
                    'c.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['p' => $this->getGroupParameterTableName()],
                'c.id = p.group_class_id',
                [
                    'p.id' => 'id',
                    'p.group_class_id' => 'group_class_id',
                    'p.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['v' => $this->getGroupValueTableName()],
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
        $revisionClassName = $this->getRevisionClass();
        $environmentClassName = $this->getEnvironmentClass();
        $groupClassClassName = $this->getGroupClassClass();
        $groupParameterClassName = $this->getGroupParameterClass();
        $aggregateRoots = [];
        foreach ($result as $row) {
            $groupId = $row['id'];
            /** @var Group $aggregateRoot */
            if (!array_key_exists($groupId, $aggregateRoots)) {
                $aggregateRoot = new $aggregateRootClassName;
                $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
                $aggregateRoots[$groupId] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);

                $environment = new $environmentClassName;
                $this->environmentHydrator->hydrate($row, $environment);
                /** @var EnvironmentProxy $environmentProxy */
                $environmentProxy = $this->environmentProxyFactory->createProxy($environment);
                $aggregateRoot->setEnvironment($environmentProxy);

                $revision = new $revisionClassName;
                $this->revisionHydrator->hydrate($row, $revision);
                /** @var RevisionProxy $revisionProxy */
                $revisionProxy = $this->revisionProxyFactory->createProxy($revision);
                $revisionProxy->setEnvironment($environmentProxy);
                $aggregateRoot->setRevision($revisionProxy);
            } else {
                $aggregateRoot = $aggregateRoots[$groupId];
            }

            if (isset($row['c.name'])) {
                $groupClass = $aggregateRoot->getClassByName($row['c.name']);
                if ($groupClass === null) {
                    $groupClass = new $groupClassClassName;
                    $this->groupClassHydrator->hydrate($row, $groupClass);
                    /** @var GroupClassProxy $groupClassProxy */
                    $groupClassProxy = $this->groupClassProxyFactory->createProxy($groupClass);
                    $aggregateRoot->addClass($groupClassProxy);
                }
                if (isset($row['p.name'])) {
                    $groupParameter = $groupClass->getParameterByName($row['p.name']);
                    if ($groupParameter === null) {
                        $groupParameter = new $groupParameterClassName;
                        $this->groupParameterHydrator->hydrate($row, $groupParameter);
                        /** @var GroupParameterProxy $groupParameterProxy */
                        $groupParameterProxy = $this->groupParameterProxyFactory->createProxy($groupParameter);
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
     * Set Revision class name.
     *
     * @param string $revisionClass
     * @return GroupRepository
     */
    public function setRevisionClass($revisionClass)
    {
        $this->revisionClass = $revisionClass;
        return $this;
    }

    /**
     * Get Revision class name.
     *
     * @return string
     */
    public function getRevisionClass()
    {
        return $this->revisionClass;
    }

    /**
     * Set RevisionProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $revisionProxyFactory
     * @return GroupRepository
     */
    public function setRevisionProxyFactory($revisionProxyFactory)
    {
        $this->revisionProxyFactory = $revisionProxyFactory;
        return $this;
    }

    /**
     * Get RevisionProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getRevisionProxyFactory()
    {
        return $this->revisionProxyFactory;
    }

    /**
     * Set RevisionHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $revisionHydrator
     * @return GroupRepository
     */
    public function setRevisionHydrator($revisionHydrator)
    {
        $this->revisionHydrator = $revisionHydrator;
        return $this;
    }

    /**
     * Get RevisionHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getRevisionHydrator()
    {
        return $this->revisionHydrator;
    }

    /**
     * Set RevisionTableName.
     *
     * @param string $revisionTableName
     * @return GroupRepository
     */
    public function setRevisionTableName($revisionTableName)
    {
        $this->revisionTableName = $revisionTableName;
        return $this;
    }

    /**
     * Get RevisionTableName.
     *
     * @return string
     */
    public function getRevisionTableName()
    {
        return $this->revisionTableName;
    }

    /**
     * Set Environment class name.
     *
     * @param string $environmentClass
     * @return GroupRepository
     */
    public function setEnvironmentClass($environmentClass)
    {
        $this->environmentClass = $environmentClass;
        return $this;
    }

    /**
     * Get Environment class name.
     *
     * @return string
     */
    public function getEnvironmentClass()
    {
        return $this->environmentClass;
    }

    /**
     * Set EnvironmentProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $environmentProxyFactory
     * @return GroupRepository
     */
    public function setEnvironmentProxyFactory($environmentProxyFactory)
    {
        $this->environmentProxyFactory = $environmentProxyFactory;
        return $this;
    }

    /**
     * Get EnvironmentProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getEnvironmentProxyFactory()
    {
        return $this->environmentProxyFactory;
    }

    /**
     * Set EnvironmentHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $environmentHydrator
     * @return GroupRepository
     */
    public function setEnvironmentHydrator($environmentHydrator)
    {
        $this->environmentHydrator = $environmentHydrator;
        return $this;
    }

    /**
     * Get EnvironmentHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getEnvironmentHydrator()
    {
        return $this->environmentHydrator;
    }

    /**
     * Set EnvironmentTableName.
     *
     * @param string $environmentTableName
     * @return GroupRepository
     */
    public function setEnvironmentTableName($environmentTableName)
    {
        $this->environmentTableName = $environmentTableName;
        return $this;
    }

    /**
     * Get EnvironmentTableName.
     *
     * @return string
     */
    public function getEnvironmentTableName()
    {
        return $this->environmentTableName;
    }

    /**
     * Set GroupClass class name.
     *
     * @param string $groupClassClass
     * @return GroupRepository
     */
    public function setGroupClassClass($groupClassClass)
    {
        $this->groupClassClass = $groupClassClass;
        return $this;
    }

    /**
     * Get GroupClass class name.
     *
     * @return string
     */
    public function getGroupClassClass()
    {
        return $this->groupClassClass;
    }

    /**
     * Set GroupClassProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $groupClassProxyFactory
     * @return GroupRepository
     */
    public function setGroupClassProxyFactory($groupClassProxyFactory)
    {
        $this->groupClassProxyFactory = $groupClassProxyFactory;
        return $this;
    }

    /**
     * Get GroupClassProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getGroupClassProxyFactory()
    {
        return $this->groupClassProxyFactory;
    }

    /**
     * Set GroupClassHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $groupClassHydrator
     * @return GroupRepository
     */
    public function setGroupClassHydrator($groupClassHydrator)
    {
        $this->groupClassHydrator = $groupClassHydrator;
        return $this;
    }

    /**
     * Get GroupClassHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getGroupClassHydrator()
    {
        return $this->groupClassHydrator;
    }

    /**
     * Set GroupClassTableName.
     *
     * @param string $groupClassTableName
     * @return GroupRepository
     */
    public function setGroupClassTableName($groupClassTableName)
    {
        $this->groupClassTableName = $groupClassTableName;
        return $this;
    }

    /**
     * Get GroupClassTableName.
     *
     * @return string
     */
    public function getGroupClassTableName()
    {
        return $this->groupClassTableName;
    }

    /**
     * Set GroupParameter class name.
     *
     * @param string $groupParameterClass
     * @return GroupRepository
     */
    public function setGroupParameterClass($groupParameterClass)
    {
        $this->groupParameterClass = $groupParameterClass;
        return $this;
    }

    /**
     * Get GroupParameter class name.
     *
     * @return string
     */
    public function getGroupParameterClass()
    {
        return $this->groupParameterClass;
    }

    /**
     * Set GroupParameterProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $groupParameterProxyFactory
     * @return GroupRepository
     */
    public function setGroupParameterProxyFactory($groupParameterProxyFactory)
    {
        $this->groupParameterProxyFactory = $groupParameterProxyFactory;
        return $this;
    }

    /**
     * Get GroupParameterProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getGroupParameterProxyFactory()
    {
        return $this->groupParameterProxyFactory;
    }

    /**
     * Set GroupParameterHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $groupParameterHydrator
     * @return GroupRepository
     */
    public function setGroupParameterHydrator($groupParameterHydrator)
    {
        $this->groupParameterHydrator = $groupParameterHydrator;
        return $this;
    }

    /**
     * Get GroupParameterHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getGroupParameterHydrator()
    {
        return $this->groupParameterHydrator;
    }

    /**
     * Set GroupParameterTableName.
     *
     * @param string $groupParameterTableName
     * @return GroupRepository
     */
    public function setGroupParameterTableName($groupParameterTableName)
    {
        $this->groupParameterTableName = $groupParameterTableName;
        return $this;
    }

    /**
     * Get GroupParameterTableName.
     *
     * @return string
     */
    public function getGroupParameterTableName()
    {
        return $this->groupParameterTableName;
    }

    /**
     * Set GroupValueTableName.
     *
     * @param string $groupValueTableName
     * @return GroupRepository
     */
    public function setGroupValueTableName($groupValueTableName)
    {
        $this->groupValueTableName = $groupValueTableName;
        return $this;
    }

    /**
     * Get GroupValueTableName.
     *
     * @return string
     */
    public function getGroupValueTableName()
    {
        return $this->groupValueTableName;
    }
}
