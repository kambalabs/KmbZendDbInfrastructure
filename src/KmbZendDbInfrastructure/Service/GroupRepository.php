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

use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use KmbDomain\Model\Group;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\RevisionInterface;
use KmbZendDbInfrastructure\Proxy\EnvironmentProxy;
use KmbZendDbInfrastructure\Proxy\PuppetClassProxy;
use KmbZendDbInfrastructure\Proxy\RevisionProxy;
use Zend\Db\Adapter\Driver\ResultInterface;
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
    protected $puppetClassClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $puppetClassProxyFactory;

    /** @var HydratorInterface */
    protected $puppetClassHydrator;

    /** @var string */
    protected $puppetClassTableName;

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
     * @param string   $name
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
     * @return Select
     */
    protected function getSelect()
    {
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
                ['c' => $this->getPuppetClassTableName()],
                $this->getTableName() . '.id = c.group_id',
                [
                    'c.id' => 'id',
                    'c.group_id' => 'group_id',
                    'c.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->order($this->getTableName() . '.ordering');
        return $select;
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    protected function hydrateAggregateRootsFromResult(ResultInterface $result)
    {
        $filteredResult = [];
        foreach ($result as $row) {
            if (!array_key_exists($row['id'], $filteredResult)) {
                $filteredResult[$row['id']] = $row;
            }
            if (isset($row['c.id'])) {
                $filteredResult[$row['id']]['classes'][] = $row;
            }
        }

        $aggregateRootClassName = $this->getAggregateRootClass();
        $revisionClassName = $this->getRevisionClass();
        $environmentClassName = $this->getEnvironmentClass();
        $puppetClassClassName = $this->getPuppetClassClass();
        $aggregateRoots = array();
        foreach ($filteredResult as $row) {
            /** @var Group $aggregateRoot */
            $aggregateRoot = new $aggregateRootClassName;
            $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);

            $environment = new $environmentClassName;
            $this->environmentHydrator->hydrate($row, $environment);
            /** @var EnvironmentProxy $environmentProxy */
            $environmentProxy = $this->environmentProxyFactory->createProxy($environment);

            $revision = new $revisionClassName;
            $this->revisionHydrator->hydrate($row, $revision);
            /** @var RevisionProxy $revisionProxy */
            $revisionProxy = $this->revisionProxyFactory->createProxy($revision);
            $revisionProxy->setEnvironment($environmentProxy);

            $aggregateRoot->setEnvironment($environmentProxy);
            $aggregateRoot->setRevision($revisionProxy);

            if (isset($row['classes'])) {
                $classes = [];
                foreach ($row['classes'] as $classRow) {
                    $class = new $puppetClassClassName;
                    $this->puppetClassHydrator->hydrate($classRow, $class);
                    /** @var PuppetClassProxy $classProxy */
                    $classProxy = $this->puppetClassProxyFactory->createProxy($class);
                    $classes[] = $classProxy;
                }
                $aggregateRoot->setClasses($classes);
            }
            $aggregateRoots[] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }
        return $aggregateRoots;
    }

    /**
     * Set RevisionClass.
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
     * Get RevisionClass.
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
     * Set EnvironmentClass.
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
     * Get EnvironmentClass.
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
     * Set PuppetClassClass.
     *
     * @param string $puppetClassClass
     * @return GroupRepository
     */
    public function setPuppetClassClass($puppetClassClass)
    {
        $this->puppetClassClass = $puppetClassClass;
        return $this;
    }

    /**
     * Get PuppetClassClass.
     *
     * @return string
     */
    public function getPuppetClassClass()
    {
        return $this->puppetClassClass;
    }

    /**
     * Set PuppetClassProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $puppetClassProxyFactory
     * @return GroupRepository
     */
    public function setPuppetClassProxyFactory($puppetClassProxyFactory)
    {
        $this->puppetClassProxyFactory = $puppetClassProxyFactory;
        return $this;
    }

    /**
     * Get PuppetClassProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getPuppetClassProxyFactory()
    {
        return $this->puppetClassProxyFactory;
    }

    /**
     * Set PuppetClassHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $puppetClassHydrator
     * @return GroupRepository
     */
    public function setPuppetClassHydrator($puppetClassHydrator)
    {
        $this->puppetClassHydrator = $puppetClassHydrator;
        return $this;
    }

    /**
     * Get PuppetClassHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getPuppetClassHydrator()
    {
        return $this->puppetClassHydrator;
    }

    /**
     * Set PuppetClassTableName.
     *
     * @param string $puppetClassTableName
     * @return GroupRepository
     */
    public function setPuppetClassTableName($puppetClassTableName)
    {
        $this->puppetClassTableName = $puppetClassTableName;
        return $this;
    }

    /**
     * Get PuppetClassTableName.
     *
     * @return string
     */
    public function getPuppetClassTableName()
    {
        return $this->puppetClassTableName;
    }
}
