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
namespace KmbZendDbInfrastructure\Proxy;

use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\Group;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionRepositoryInterface;

class GroupProxy implements GroupInterface
{
    /** @var Group */
    protected $aggregateRoot;

    /** @var  RevisionInterface */
    protected $revision;

    /** @var  EnvironmentInterface */
    protected $environment;

    /** @var  RevisionRepositoryInterface */
    protected $revisionRepository;

    /**
     * Set AggregateRoot.
     *
     * @param \GtnPersistBase\Model\AggregateRootInterface $aggregateRoot
     * @return GroupProxy
     */
    public function setAggregateRoot($aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
        return $this;
    }

    /**
     * Get AggregateRoot.
     *
     * @return \GtnPersistBase\Model\AggregateRootInterface
     */
    public function getAggregateRoot()
    {
        return $this->aggregateRoot;
    }

    /**
     * @param int $id
     * @return GroupProxy
     */
    public function setId($id)
    {
        $this->aggregateRoot->setId($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->aggregateRoot->getId();
    }

    /**
     * @param RevisionInterface $revision
     * @return GroupProxy
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
        return $this;
    }

    /**
     * @return RevisionInterface
     */
    public function getRevision()
    {
        if ($this->revision == null) {
            $this->setRevision($this->revisionRepository->getByGroup($this));
        }
        return $this->revision;
    }

    /**
     * Set Environment.
     *
     * @param \KmbDomain\Model\EnvironmentInterface $environment
     * @return GroupProxy
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Get Environment.
     *
     * @return \KmbDomain\Model\EnvironmentInterface
     */
    public function getEnvironment()
    {
        if ($this->environment == null) {
            $this->setEnvironment($this->getRevision()->getEnvironment());
        }
        return $this->environment;
    }

    /**
     * @param string $name
     * @return GroupProxy
     */
    public function setName($name)
    {
        $this->aggregateRoot->setName($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->aggregateRoot->getName();
    }

    /**
     * @param int $ordering
     * @return GroupProxy
     */
    public function setOrdering($ordering)
    {
        $this->aggregateRoot->setOrdering($ordering);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return $this->aggregateRoot->getOrdering();
    }

    /**
     * @param string $pattern
     * @return GroupProxy
     */
    public function setIncludePattern($pattern)
    {
        $this->aggregateRoot->setIncludePattern($pattern);
        return $this;
    }

    /**
     * @return string
     */
    public function getIncludePattern()
    {
        return $this->aggregateRoot->getIncludePattern();
    }

    /**
     * @param string $pattern
     * @return GroupProxy
     */
    public function setExcludePattern($pattern)
    {
        $this->aggregateRoot->setExcludePattern($pattern);
        return $this;
    }

    /**
     * @return string
     */
    public function getExcludePattern()
    {
        return $this->aggregateRoot->getExcludePattern();
    }

    /**
     * Set Classes.
     *
     * @param \KmbDomain\Model\GroupClassInterface[] $classes
     * @return GroupProxy
     */
    public function setClasses($classes)
    {
        $this->aggregateRoot->setClasses($classes);
        return $this;
    }

    /**
     * Add specified class.
     *
     * @param \KmbDomain\Model\GroupClassInterface $class
     * @return GroupProxy
     */
    public function addClass($class)
    {
        $this->aggregateRoot->addClass($class);
        return $this;
    }

    /**
     * Get Classes.
     *
     * @return \KmbDomain\Model\GroupClassInterface[]
     */
    public function getClasses()
    {
        return $this->aggregateRoot->getClasses();
    }

    /**
     * @return bool
     */
    public function hasClasses()
    {
        return $this->aggregateRoot->hasClasses();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasClassWithName($name)
    {
        return $this->aggregateRoot->hasClassWithName($name);
    }

    /**
     * @param string $name
     * @return \KmbDomain\Model\GroupClassInterface
     */
    public function getClassByName($name)
    {
        return $this->aggregateRoot->getClassByName($name);
    }

    public function __clone()
    {
        $this->environment = null;
        $this->revision = null;
        $this->aggregateRoot = clone $this->aggregateRoot;
    }

    /**
     * Set RevisionRepository.
     *
     * @param \KmbDomain\Model\RevisionRepositoryInterface $revisionRepository
     * @return GroupProxy
     */
    public function setRevisionRepository($revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        return $this;
    }

    /**
     * Get RevisionRepository.
     *
     * @return \KmbDomain\Model\RevisionRepositoryInterface
     */
    public function getRevisionRepository()
    {
        return $this->revisionRepository;
    }
}
