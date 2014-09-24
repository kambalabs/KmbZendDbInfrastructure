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
use KmbZendDbInfrastructure\Proxy\ParameterProxy;
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

    /** @var string */
    protected $parameterClass;

    /** @var AggregateRootProxyFactoryInterface */
    protected $parameterProxyFactory;

    /** @var HydratorInterface */
    protected $parameterHydrator;

    /** @var string */
    protected $parameterTableName;

    /** @var string */
    protected $valueClass;

    /** @var HydratorInterface */
    protected $valueHydrator;

    /** @var string */
    protected $valueTableName;

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
                ['c' => $this->getPuppetClassTableName()],
                $this->getTableName() . '.id = c.group_id',
                [
                    'c.id' => 'id',
                    'c.group_id' => 'group_id',
                    'c.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['p' => $this->getParameterTableName()],
                'c.id = p.puppet_class_id',
                [
                    'p.id' => 'id',
                    'p.puppet_class_id' => 'puppet_class_id',
                    'p.name' => 'name',
                ],
                Select::JOIN_LEFT
            )
            ->join(
                ['v' => $this->getValueTableName()],
                'p.id = v.parameter_id',
                [
                    'v.id' => 'id',
                    'v.parameter_id' => 'parameter_id',
                    'v.name' => 'name',
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
        $revisionClassName      = $this->getRevisionClass();
        $environmentClassName   = $this->getEnvironmentClass();
        $puppetClassClassName   = $this->getPuppetClassClass();
        $parameterClassName     = $this->getParameterClass();
        $valueClassName         = $this->getValueClass();
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
                $class = $aggregateRoot->getClassByName($row['c.name']);
                if ($class === null) {
                    $class = new $puppetClassClassName;
                    $this->puppetClassHydrator->hydrate($row, $class);
                    /** @var PuppetClassProxy $classProxy */
                    $classProxy = $this->puppetClassProxyFactory->createProxy($class);
                    $aggregateRoot->addClass($classProxy);
                }
                if (isset($row['p.name'])) {
                    $parameter = $class->getParameterByName($row['p.name']);
                    if ($parameter === null) {
                        $parameter = new $parameterClassName;
                        $this->parameterHydrator->hydrate($row, $parameter);
                        /** @var ParameterProxy $parameterProxy */
                        $parameterProxy = $this->parameterProxyFactory->createProxy($parameter);
                        $class->addParameter($parameterProxy);
                    }
                    if (isset($row['v.name'])) {
                        $value = $parameter->getValueByName($row['v.name']);
                        if ($value === null) {
                            $value = new $valueClassName;
                            $this->valueHydrator->hydrate($row, $value);
                            $parameter->addValue($value);
                        }
                    }
                }
            }
        }
        return array_values($aggregateRoots);
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

    /**
     * Set ParameterClass.
     *
     * @param string $parameterClass
     * @return GroupRepository
     */
    public function setParameterClass($parameterClass)
    {
        $this->parameterClass = $parameterClass;
        return $this;
    }

    /**
     * Get ParameterClass.
     *
     * @return string
     */
    public function getParameterClass()
    {
        return $this->parameterClass;
    }

    /**
     * Set ParameterProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $parameterProxyFactory
     * @return GroupRepository
     */
    public function setParameterProxyFactory($parameterProxyFactory)
    {
        $this->parameterProxyFactory = $parameterProxyFactory;
        return $this;
    }

    /**
     * Get ParameterProxyFactory.
     *
     * @return \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface
     */
    public function getParameterProxyFactory()
    {
        return $this->parameterProxyFactory;
    }

    /**
     * Set ParameterHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $parameterHydrator
     * @return GroupRepository
     */
    public function setParameterHydrator($parameterHydrator)
    {
        $this->parameterHydrator = $parameterHydrator;
        return $this;
    }

    /**
     * Get ParameterHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getParameterHydrator()
    {
        return $this->parameterHydrator;
    }

    /**
     * Set ParameterTableName.
     *
     * @param string $parameterTableName
     * @return GroupRepository
     */
    public function setParameterTableName($parameterTableName)
    {
        $this->parameterTableName = $parameterTableName;
        return $this;
    }

    /**
     * Get ParameterTableName.
     *
     * @return string
     */
    public function getParameterTableName()
    {
        return $this->parameterTableName;
    }

    /**
     * Set ValueClass.
     *
     * @param string $valueClass
     * @return GroupRepository
     */
    public function setValueClass($valueClass)
    {
        $this->valueClass = $valueClass;
        return $this;
    }

    /**
     * Get ValueClass.
     *
     * @return string
     */
    public function getValueClass()
    {
        return $this->valueClass;
    }

    /**
     * Set ValueHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $valueHydrator
     * @return GroupRepository
     */
    public function setValueHydrator($valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
        return $this;
    }

    /**
     * Get ValueHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getValueHydrator()
    {
        return $this->valueHydrator;
    }

    /**
     * Set ValueTableName.
     *
     * @param string $valueTableName
     * @return GroupRepository
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
