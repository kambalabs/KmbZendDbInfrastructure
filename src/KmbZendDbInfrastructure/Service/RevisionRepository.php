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
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionRepositoryInterface;
use KmbZendDbInfrastructure\Proxy\EnvironmentProxy;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Stdlib\Hydrator\HydratorInterface;

class RevisionRepository extends Repository implements RevisionRepositoryInterface
{
    /** @var AggregateRootProxyFactoryInterface */
    protected $environmentProxyFactory;

    /** @var HydratorInterface */
    protected $environmentHydrator;

    /** @var string */
    protected $environmentClass;

    /** @var string */
    protected $environmentTableName;

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
        $select = $this->getSelect()->where($where)->order('released_at DESC');
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
     * @return Select
     */
    protected function getSelect()
    {
        return parent::getSelect()->join(
            ['e' => $this->getEnvironmentTableName()],
            $this->getTableName() . '.environment_id = e.id',
            [
                'e.id' => 'id',
                'e.name' => 'name',
                'e.isdefault' => 'isdefault',
            ]
        );
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    protected function hydrateAggregateRootsFromResult(ResultInterface $result)
    {
        $aggregateRootClassName = $this->getAggregateRootClass();
        $environmentClassName = $this->getEnvironmentClass();
        $aggregateRoots = array();
        foreach ($result as $row) {
            /** @var Revision $aggregateRoot */
            $aggregateRoot = new $aggregateRootClassName();
            $this->aggregateRootHydrator->hydrate($row, $aggregateRoot);
            $environment = new $environmentClassName();
            $this->environmentHydrator->hydrate($row, $environment);
            /** @var EnvironmentProxy $environmentProxy */
            $environmentProxy = $this->environmentProxyFactory->createProxy($environment);
            $aggregateRoot->setEnvironment($environmentProxy);
            $aggregateRoots[] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }
        return $aggregateRoots;
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
}
