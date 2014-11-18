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
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionLog;
use KmbDomain\Model\RevisionRepositoryInterface;
use KmbZendDbInfrastructure\Proxy\EnvironmentProxy;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Stdlib\Hydrator\HydratorInterface;

class RevisionRepository extends Repository implements RevisionRepositoryInterface
{
    /** @var  string */
    protected $revisionLogClass;

    /** @var  HydratorInterface */
    protected $revisionLogHydrator;

    /** @var  string */
    protected $revisionLogTableName;

    /** @var  string */
    protected $revisionLogTableSequenceName;

    /** @var AggregateRootProxyFactoryInterface */
    protected $environmentProxyFactory;

    /** @var HydratorInterface */
    protected $environmentHydrator;

    /** @var string */
    protected $environmentClass;

    /** @var string */
    protected $environmentTableName;

    /** @var  GroupRepository */
    protected $groupRepository;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        /** @var RevisionInterface $aggregateRoot */
        parent::add($aggregateRoot);

        if ($aggregateRoot->hasGroups()) {
            foreach ($aggregateRoot->getGroups() as $group) {
                $group->setRevision($aggregateRoot);
                $this->groupRepository->add($group);
            }
        }

        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return RepositoryInterface
     */
    public function update(AggregateRootInterface $aggregateRoot)
    {
        /** @var RevisionInterface $aggregateRoot */
        parent::update($aggregateRoot);

        if ($aggregateRoot->hasLogs()) {
            foreach ($aggregateRoot->getLogs() as $log) {
                $log->setRevision($aggregateRoot);
                if ($log->getId() === null) {
                    $data = $this->revisionLogHydrator->extract($log);
                    $insert = $this->getMasterSql()->insert($this->revisionLogTableName)->values($data);
                    $this->performWrite($insert);
                    if ($log->getId() === null) {
                        $log->setId($this->getDbAdapter()->getDriver()->getLastGeneratedValue($this->revisionLogTableSequenceName));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface[]
     */
    public function getAllReleasedByEnvironment(EnvironmentInterface $environment)
    {
        $where = new Where();
        $where
            ->equalTo('environment_id', $environment->getId())
            ->and
            ->isNotNull('released_at');
        $select = $this->getSelect()->where($where);
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface
     */
    public function getCurrentByEnvironment(EnvironmentInterface $environment)
    {
        $where = new Where();
        $where
            ->equalTo('environment_id', $environment->getId())
            ->and
            ->isNull('released_at');
        return $this->getBy($where);
    }

    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface
     */
    public function getLastReleasedByEnvironment(EnvironmentInterface $environment)
    {
        $revisions = $this->getAllReleasedByEnvironment($environment);
        return empty($revisions) ? null : $revisions[0];
    }

    /**
     * @param GroupInterface $group
     * @return RevisionInterface
     */
    public function getByGroup(GroupInterface $group)
    {
        $select = $this->getSelect()->join(
            ['g' => $this->groupRepository->getTableName()],
            $this->tableName . '.id = g.revision_id',
            []
        )->where(['g.id' => $group->getId()]);
        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return parent::getSelect()
            ->join(
                ['e' => $this->getEnvironmentTableName()],
                $this->getTableName() . '.environment_id = e.id',
                [
                    'e.id' => 'id',
                    'e.name' => 'name',
                    'e.isdefault' => 'isdefault',
                ]
            )
            ->join(
                ['rl' => $this->getRevisionLogTableName()],
                $this->getTableName() . '.id = rl.revision_id',
                [
                    'rl.id' => 'id',
                    'rl.created_at' => 'created_at',
                    'rl.created_by' => 'created_by',
                    'rl.comment' => 'comment',
                ],
                Select::JOIN_LEFT
            )
            ->order('released_at DESC, rl.created_at DESC');
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    protected function hydrateAggregateRootsFromResult(ResultInterface $result)
    {
        $aggregateRootClassName = $this->getAggregateRootClass();
        $revisionLogClassName = $this->getRevisionLogClass();
        $environmentClassName = $this->getEnvironmentClass();
        $aggregateRoots = [];
        foreach ($result as $row) {
            $revisionId = $row['id'];
            /** @var RevisionInterface $aggregateRoot */
            if (!array_key_exists($revisionId, $aggregateRoots)) {
                $aggregateRoot = new $aggregateRootClassName();
                $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
                $environment = new $environmentClassName();
                $this->environmentHydrator->hydrate($row, $environment);
                /** @var EnvironmentProxy $environmentProxy */
                $environmentProxy = $this->environmentProxyFactory->createProxy($environment);
                $aggregateRoot->setEnvironment($environmentProxy);
                $aggregateRoots[$revisionId] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
            } else {
                $aggregateRoot = $aggregateRoots[$revisionId];
            }
            if (isset($row['rl.created_at'])) {
                /** @var RevisionLog $revisionLog */
                $revisionLog = new $revisionLogClassName;
                $this->revisionLogHydrator->hydrate($row, $revisionLog);
                $aggregateRoot->addLog($revisionLog);
            }
        }
        return array_values($aggregateRoots);
    }

    /**
     * Set RevisionLogClass.
     *
     * @param string $revisionLogClass
     * @return RevisionRepository
     */
    public function setRevisionLogClass($revisionLogClass)
    {
        $this->revisionLogClass = $revisionLogClass;
        return $this;
    }

    /**
     * Get RevisionLogClass.
     *
     * @return string
     */
    public function getRevisionLogClass()
    {
        return $this->revisionLogClass;
    }

    /**
     * Set RevisionLogHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $revisionLogHydrator
     * @return RevisionRepository
     */
    public function setRevisionLogHydrator($revisionLogHydrator)
    {
        $this->revisionLogHydrator = $revisionLogHydrator;
        return $this;
    }

    /**
     * Get RevisionLogHydrator.
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getRevisionLogHydrator()
    {
        return $this->revisionLogHydrator;
    }

    /**
     * Set RevisionLogTableName.
     *
     * @param string $revisionLogTableName
     * @return RevisionRepository
     */
    public function setRevisionLogTableName($revisionLogTableName)
    {
        $this->revisionLogTableName = $revisionLogTableName;
        return $this;
    }

    /**
     * Get RevisionLogTableName.
     *
     * @return string
     */
    public function getRevisionLogTableName()
    {
        return $this->revisionLogTableName;
    }

    /**
     * Set RevisionLogTableSequenceName.
     *
     * @param string $revisionLogTableSequenceName
     * @return RevisionRepository
     */
    public function setRevisionLogTableSequenceName($revisionLogTableSequenceName)
    {
        $this->revisionLogTableSequenceName = $revisionLogTableSequenceName;
        return $this;
    }

    /**
     * Get RevisionLogTableSequenceName.
     *
     * @return string
     */
    public function getRevisionLogTableSequenceName()
    {
        return $this->revisionLogTableSequenceName;
    }

    /**
     * Set EnvironmentHydrator.
     *
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $environmentHydrator
     * @return RevisionRepository
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
     * Set EnvironmentProxyFactory.
     *
     * @param \GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface $environmentProxyFactory
     * @return RevisionRepository
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
     * Set EnvironmentClass.
     *
     * @param string $environmentClass
     * @return RevisionRepository
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
     * Set EnvironmentTableName.
     *
     * @param string $environmentTableName
     * @return RevisionRepository
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
     * Set GroupRepository.
     *
     * @param \KmbZendDbInfrastructure\Service\GroupRepository $groupRepository
     * @return RevisionRepository
     */
    public function setGroupRepository($groupRepository)
    {
        $this->groupRepository = $groupRepository;
        return $this;
    }

    /**
     * Get GroupRepository.
     *
     * @return \KmbZendDbInfrastructure\Service\GroupRepository
     */
    public function getGroupRepository()
    {
        return $this->groupRepository;
    }
}
