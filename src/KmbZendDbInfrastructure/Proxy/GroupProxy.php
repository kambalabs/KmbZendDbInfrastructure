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

use KmbDomain\Model\Group;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\RevisionInterface;

class GroupProxy implements GroupInterface
{
    /** @var Group */
    protected $aggregateRoot;

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
        $this->aggregateRoot->setRevision($revision);
        return $this;
    }

    /**
     * @return RevisionInterface
     */
    public function getRevision()
    {
        return $this->aggregateRoot->getRevision();
    }

    /**
     * Set Environment.
     *
     * @param \KmbDomain\Model\EnvironmentInterface $environment
     * @return GroupProxy
     */
    public function setEnvironment($environment)
    {
        $this->aggregateRoot->setEnvironment($environment);
        return $this;
    }

    /**
     * Get Environment.
     *
     * @return \KmbDomain\Model\EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->aggregateRoot->getEnvironment();
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
     * @param \KmbDomain\Model\PuppetClassInterface[] $classes
     * @return GroupProxy
     */
    public function setClasses($classes)
    {
        $this->aggregateRoot->setClasses($classes);
        return $this;
    }

    /**
     * Get Classes.
     *
     * @return \KmbDomain\Model\PuppetClassInterface[]
     */
    public function getClasses()
    {
        return $this->aggregateRoot->getClasses();
    }
}
